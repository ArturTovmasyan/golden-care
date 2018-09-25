<?php
namespace App\Api\V1\Controller\Rest;

use App\Api\V1\Controller\Rest\Exception\DuplicateUserException;
use App\Api\V1\Controller\Rest\Exception\IncorrectPasswordException;
use App\Api\V1\Controller\Rest\Exception\InvalidDataException;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
    public function signupAction(Request $request, UserPasswordEncoderInterface $encoder)
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
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

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

            /** @var User $user */
            $user = new User();
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setUsername(strtolower($firstName) . time());
            $user->setEmail($email);
            $user->setLastActivityAt(new \DateTime());
            $user->setRoles([]);
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
     * @Route("/api/v1.0/security/change-password", name="security_change_password")
     * @param $request
     * @return array | JsonResponse
     * @throws
     */
    public function changePasswordAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $userId          = $request->get('id');
        $password        = $request->get('password');
        $newPassword     = $request->get('newPassword');
        $confirmPassword = $request->get('confirmPassword');

        /** @var User $user */
        $user = $em->getRepository(User::class)->find($userId);

        if ($user) {

            $allErrors = [];

            /** @var EncoderFactory $encoderService */
            $encoderService = $this->get('security.encoder_factory');
            $encoder = $encoderService->getEncoder($user);

            //check password is valid
            if ($encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
                //check if new and confirm password is equal
                if ($newPassword === $confirmPassword) {
                    // check with old password
                    if ($newPassword !== $password) {
                        //encode and save new password
                        $user->setPlainPassword($newPassword);

                        if (!$user->isAdmin()) {
                            $user->setVerified(true);
                        }

                        $em->getConnection()->beginTransaction();

                        try {
                            $em->persist($user);
                            $em->flush();

                            $em->getConnection()->commit();

                            $status = Response::HTTP_CREATED;
                            $response = ['status' => $status, 'message' => 'Success'];

                        } catch (\Exception $e) {
                            $em->getConnection()->rollBack();

                            $status = Response::HTTP_BAD_REQUEST;
                            $response = ['status' => $e->getCode(), 'message' => $e->getMessage()];
                        }
                    } else {
                        $allErrors['newPassword'] = 'New password must be different from last password';
                        $status = Response::HTTP_BAD_REQUEST;
                        $response = ['status' => Response::HTTP_BAD_REQUEST, 'message' => $allErrors['newPassword'], 'data' => ['errors' => $allErrors]];
                    }
                } else {
                    $allErrors['newPassword'] = 'New password is not confirmed';
                    $status = Response::HTTP_BAD_REQUEST;
                    $response = ['status' => Response::HTTP_BAD_REQUEST, 'message' => $allErrors['newPassword'], 'data' => ['errors' => $allErrors]];
                }

            } else {
                $allErrors['password'] = 'Invalid current password';
                $status = Response::HTTP_BAD_REQUEST;
                $response = ['status' => Response::HTTP_BAD_REQUEST, 'message' => $allErrors['password'], 'data' => ['errors' => $allErrors]];
            }

        } else {
            $allErrors['userNotFound'] = "User by id $userId not found";
            $status = Response::HTTP_NOT_FOUND;
            $response = ['status' => Response::HTTP_NOT_FOUND, 'message' => $allErrors['userNotFound'], 'data' => ['errors' => $allErrors]];
        }

        return new JsonResponse($response, $status);
    }

    /**
     * This function is used to forgot password
     *
     *
     * @Method("POST")
     * @Route("/security/forgot-password", name="security_forgot_password")
     * @param $request
     * @return array | JsonResponse
     * @throws
     */
    public function forgotPasswordAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        // get user credentials
        $email = $request->get('email');

        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($user) {
            $user->setPasswordRecoveryHash($email);

            try {
                $em->getConnection()->beginTransaction();
                $em->persist($user);
                $em->flush();

                $em->getConnection()->commit();

                $status = Response::HTTP_CREATED;
                $response = ['status' => $status, 'message' => 'Password recovery link sent, please check email.'];
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();

                $status = Response::HTTP_BAD_REQUEST;
                $response = ['status' => $e->getCode(), 'message' => $e->getMessage()];
            }

        } else {
            $status   = Response::HTTP_NOT_FOUND;
            $response = ['status' => Response::HTTP_NOT_FOUND, 'message' => "User by id $email not found"];
        }

        return new JsonResponse($response, $status);
    }


    /**
     * This function is used to change password
     *
     *
     * @Method("PUT")
     * @Route("/api/v1.0/security/reset-password", name="security_reset_password")
     * @param $request
     * @return array | JsonResponse
     * @throws
     */
    public function resetPasswordAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        //get user credentials
        $userId = $request->get('id');

        /** @var User $user */
        $user = $em->getRepository(User::class)->find($userId);

        if ($user) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $status = Response::HTTP_OK;
            $response = ['status' => Response::HTTP_NOT_FOUND, 'message' => "Success"];
        } else {
            $status = Response::HTTP_NOT_FOUND;
            $response = ['status' => Response::HTTP_NOT_FOUND, 'message' => "User by id $userId not found"];
        }

        return new JsonResponse($response, $status);
    }

    /**
     * This function is used to confirm password with hash
     *
     *
     * @Method("PUT")
     * @Route("/api/v1.0/security/confirm-password", name="security_confirm_password")
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

            // encode and save new password
            $user->setPasswordRecoveryHash(null);

            try {
                $em->getConnection()->beginTransaction();
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