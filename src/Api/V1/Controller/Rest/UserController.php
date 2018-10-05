<?php
namespace App\Api\V1\Controller\Rest;

use App\Api\V1\Controller\Rest\Exception\InvalidDataException;
use App\Api\V1\Service\UserService;
use App\Entity\User;
use App\Util\Mailer;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Annotation\Permission;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UserController
 * @package App\Api\V1\Controller\Rest
 * @Route("/api/v1.0")
 * @Permission({"PERMISSION_USER"})
 */
class UserController extends BaseController
{
    /**
     * @Method("GET")
     * @Route("/space/{spaceId}/user", name="user_list")
     */
    public function getAction()
    {
        /** @var User[] $users */
        $users = $this->em->getRepository(User::class)->findAll();

        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $data = $serializer->serialize(
            [
                'status'  => JsonResponse::HTTP_OK,
                'message' => 'Success',
                'data'    => [
                    'users' => $users
                ]
            ],
            'json',
            SerializationContext::create()->setGroups(['api_user__list'])
        );

        $response = new JsonResponse();
        $response->setContent($data);

        return $response;
    }

    /**
     * This function is used to reset password
     *
     * @Method("PUT")
     * @Route("/user/reset-password/{id}", name="user_reset_password", requirements={"id"="\d+"})
     *
     * @param $id
     * @param UserService $userService
     * @return JsonResponse
     */
    public function resetPasswordAction($id, UserService $userService)
    {
        try {
            /** @var User $user */
            $user = $this->em->getRepository(User::class)->find($id);

            if (is_null($user)) {
                throw new InvalidDataException("User by id $id not found", Response::HTTP_BAD_REQUEST);
            }

            try {
                $this->em->getConnection()->beginTransaction();

                $password = $userService->generatePassword(8);
                $encoded  = $this->encoder->encodePassword($user, $password);

                $user->setPlainPassword($password);
                $user->setPassword($encoded);
                $this->em->persist($user);
                $this->em->flush();

                $this->em->getConnection()->commit();

                $mailer = new Mailer($this->container);
                $mailer->notifyCredentials($user);

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
}