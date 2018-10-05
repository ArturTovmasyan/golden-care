<?php
namespace App\Api\V1\Controller\Rest;

use App\Api\V1\Controller\Rest\Exception\DuplicateUserException;
use App\Api\V1\Controller\Rest\Exception\IncorrectPasswordException;
use App\Api\V1\Controller\Rest\Exception\InvalidDataException;
use App\Api\V1\Service\UserService;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\SpaceUserRole;
use App\Entity\User;
use App\Entity\UserLog;
use App\Model\Log;
use App\Util\Mailer;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class SecurityController
 * @package App\Api\V1\Controller\Rest
 *
 * @Route("/api/v1.0/security")
 */
class SecurityController extends BaseController
{
    /**
     * This function is used to login user
     *
     * @Method("POST")
     * @Route("/signup", name="security_signup")
     * @param $request
     * @return array | JsonResponse
     * @throws
     */
    public function signupAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        try {
            $firstName  = $request->get('firstName');
            $lastName   = $request->get('lastName');
            $email      = $request->get('email');
            $password   = $request->get('password');
            $rePassword = $request->get('rePassword');

            /** @var User $user */
            $user                = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            $defaultRoleForSpace = $em->getRepository(Role::class)->getSpaceDefaultRole();

            if ($user) {
                throw new DuplicateUserException(
                    'User with this email address already exist',
                    Response::HTTP_BAD_REQUEST
                );
            }

            if ($password != $rePassword) {
                throw new IncorrectPasswordException(
                    'Password and repeat password don\'t match',
                    Response::HTTP_BAD_REQUEST
                );
            }

            // create user
            $user = new User();
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setUsername(strtolower($firstName) . time());
            $user->setEmail($email);
            $user->setLastActivityAt(new \DateTime());
            $user->setEnabled(false);

            $encoded = $this->encoder->encodePassword($user, $password);
            $user->setPassword($encoded);

            /** @var ValidatorInterface $validator */
            $validator    = $this->get('validator');
            $errors       = $validator->validate($user, null, ["signup"]);
            $returnErrors = [];

            if ($errors->count() > 0) {
                foreach ($errors as $error) {
                    $returnErrors[$error->getPropertyPath()] = $error->getMessage();
                }

                throw new InvalidDataException(
                    'Invalid data',
                    Response::HTTP_BAD_REQUEST,
                    $returnErrors
                );
            }

            try {
                $em->getConnection()->beginTransaction();
                $em->persist($user);

                // create space
                $space = new Space();
                $em->persist($space);

                // create space user roles
                if ($defaultRoleForSpace) {
                    $spaceUserRole = new SpaceUserRole();
                    $spaceUserRole->setUser($user);
                    $spaceUserRole->setRole($defaultRoleForSpace);
                    $spaceUserRole->setSpace($space);
                    $em->persist($spaceUserRole);
                }

                // create log
                $log = new UserLog();
                $log->setCreatedAt(new \DateTime());
                $log->setUser($user);
                $log->setType(UserLog::LOG_TYPE_AUTHENTICATION);
                $log->setLevel(Log::LOG_LEVEL_LOW);
                $log->setMessage(sprintf("User %s (%s) registered in ", $user->getFullName(), $user->getUsername()));
                $em->persist($log);

                $em->flush();
                $em->getConnection()->commit();
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();

                throw new InvalidDataException(
                    'System Error',
                    Response::HTTP_BAD_REQUEST
                );
            }

            $status   = Response::HTTP_CREATED;
            $response = [
                'message' => 'Please waiting to approval',
                'status'  => $status
            ];
        } catch (\Throwable $e) {
            $status   = $e->getCode();
            $response = [
                'status'  => $status,
                'message' => $e->getMessage()
            ];

            if ($e instanceof InvalidDataException && !empty($e->getErrors())) {
                $response['errors'] = $e->getErrors();
            }
        }

        return new JsonResponse($response, $status);
    }

    /**
     * This function is used to change password
     *
     * @Method("PUT")
     * @Route("/change-password", name="security_change_password")
     * @param $request
     * @return array | JsonResponse
     * @throws
     */
    public function changePasswordAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        try {
            $password        = $request->get('password');
            $newPassword     = $request->get('newPassword');
            $confirmPassword = $request->get('confirmPassword');

            /** @var User $user */
            $user = $this->get('security.token_storage')->getToken()->getUser();

            if (!$this->encoder->isPasswordValid($user, $password)) {
                $allErrors['password'] = 'Invalid current password';

                throw new InvalidDataException(
                    $allErrors['password'],
                    Response::HTTP_BAD_REQUEST,
                    $allErrors
                );
            }

            if ($newPassword !== $confirmPassword) {
                $allErrors['newPassword'] = 'New password is not confirmed';

                throw new InvalidDataException(
                    $allErrors['newPassword'],
                    Response::HTTP_BAD_REQUEST,
                    $allErrors
                );
            }

            if ($newPassword == $password) {
                $allErrors['newPassword'] = 'New password must be different from last password';

                throw new InvalidDataException(
                    $allErrors['newPassword'],
                    Response::HTTP_BAD_REQUEST,
                    $allErrors
                );
            }

            try {
                $em->getConnection()->beginTransaction();

                $encoded = $this->encoder->encodePassword($user, $newPassword);
                $user->setPassword($encoded);

                $em->persist($user);
                $em->flush();

                $em->getConnection()->commit();

                $status   = Response::HTTP_CREATED;
                $response = [
                    'status'  => $status,
                    'message' => 'Success'
                ];
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();

                throw new InvalidDataException(
                    'System Error',
                    Response::HTTP_BAD_REQUEST
                );
            }
        } catch (\Throwable $e) {
            $status   = $e->getCode();
            $response = [
                'status'  => $status,
                'message' => $e->getMessage()
            ];

            if ($e instanceof InvalidDataException && !empty($e->getErrors())) {
                $response['errors'] = $e->getErrors();
            }
        }

        return new JsonResponse($response, $status);
    }

    /**
     * This function is used to forgot password
     *
     *
     * @Method("POST")
     * @Route("/forgot-password", name="security_forgot_password")
     * @param $request
     * @return array | JsonResponse
     * @throws
     */
    public function forgotPasswordAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        try {
            // get user credentials
            $email = $request->get('email');

            /** @var User $user */
            $user = $em->getRepository(User::class)->findOneByEmail($email);

            if (is_null($user)) {
                throw new InvalidDataException("User by id $email not found", Response::HTTP_BAD_REQUEST);
            }

            try {
                $em->getConnection()->beginTransaction();

                $user->setPasswordRecoveryHash();
                $em->persist($user);
                $em->flush();

                $mailer = new Mailer($this->container);
                $mailer->sendPasswordRecoveryLink($user, $request->getSchemeAndHttpHost());

                $em->getConnection()->commit();

                $status = Response::HTTP_CREATED;
                $response = [
                    'status'  => $status,
                    'message' => 'Password recovery link sent, please check email.'
                ];
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();

                throw new InvalidDataException(
                    'System Error',
                    Response::HTTP_BAD_REQUEST
                );
            }
        } catch (\Throwable $e) {
            $status   = $e->getCode();
            $response = [
                'status'  => $status,
                'message' => $e->getMessage()
            ];

            if ($e instanceof InvalidDataException && !empty($e->getErrors())) {
                $response['errors'] = $e->getErrors();
            }
        }

        return new JsonResponse($response, $status);
    }

    /**
     * This function is used to confirm password with hash
     *
     *
     * @Method("PUT")
     * @Route("/confirm-password", name="security_confirm_password")
     * @param $request
     * @return array | JsonResponse
     * @throws
     */
    public function confirmPasswordAction(Request $request)
    {
        try {
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();

            $hash            = $request->get('hash');
            $newPassword     = $request->get('newPassword');
            $confirmPassword = $request->get('confirmPassword');

            /** @var User $user */
            $user = $em->getRepository(User::class)->findOneBy(['passwordRecoveryHash' => $hash]);

            if (!$user) {
                throw new \Exception("User by hash $hash not found", Response::HTTP_NOT_FOUND);
            }

            // check if new and confirm password is equal
            if ($newPassword !== $confirmPassword) {
                throw new \Exception('New password is not confirmed.', Response::HTTP_BAD_REQUEST);
            }

            try {
                $em->getConnection()->beginTransaction();

                $user->setPasswordRecoveryHash();
                $em->persist($user);
                $em->flush();

                $em->getConnection()->commit();

                $status = Response::HTTP_CREATED;
                $response = ['status' => $status, 'message' => 'Success'];
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();
                $status = Response::HTTP_BAD_REQUEST;
                throw new \Exception($e->getMessage(), $status);
            }
        } catch (\Throwable $e) {
            $status = $e->getCode();
            $response = ['status' => $status, 'message' => $e->getMessage()];
        }

        return new JsonResponse($response, $status);
    }
}