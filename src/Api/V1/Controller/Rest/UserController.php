<?php
namespace App\Api\V1\Controller\Rest;

use App\Api\V1\Service\UserService;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
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
     * @Route("/space/{spaceId}/user", name="user_list", requirements={"spaceId"="\d+"})
     *
     * @param $spaceId
     * @return JsonResponse
     */
    public function listAction($spaceId)
    {
        $users = $this->em->getRepository(User::class)->findAll();

        return $this->respondSuccess(
            '',
            Response::HTTP_OK,
            ['users' => $users],
            ['api_user__list']
        );
    }

    /**
     * @Method("GET")
     * @Route("/user/{id}", name="user_info", requirements={"id"="\d+"})
     *
     * @param $id
     * @return JsonResponse
     */
    public function getAction($id)
    {
        $user = $this->em->getRepository(User::class)->find($id);

        return $this->respondSuccess(
            '',
            Response::HTTP_OK,
            ['user' => $user],
            ['api_user__info']
        );
    }

    /**
     * This function is used to reset password
     *
     * @Method("POST")
     * @Route("/space/{spaceId}/user/invite", name="user_invite", requirements={"spaceId"="\d+"})
     *
     * @param $spaceId
     * @param UserService $userService
     * @param Request $request
     * @return JsonResponse
     */
    public function inviteAction($spaceId, UserService $userService, Request $request)
    {
        try {
            $this->normalizeJson($request);

            $userService->invite(
                $spaceId,
                $request->get('email'),
                $request->get('roleId')
            );

            $response = $this->respondSuccess(
                'Invitation sent to email address, please check email.',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * This function is used to reset password
     *
     * @Method("POST")
     * @Route("/space/{spaceId}/accept/{roleId}", name="user_accept", requirements={"spaceId"="\d+", "roleId"="\d+"})
     *
     * @param $spaceId
     * @param $roleId
     * @param UserService $userService
     * @return JsonResponse
     */
    public function acceptInvitationAction($spaceId, $roleId, UserService $userService)
    {
        try {
            $userService->acceptInvitation($spaceId, $roleId);

            $response = $this->respondSuccess(
                'Invitation successfully accepted',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * This function is used to reset password
     *
     * @Method("POST")
     * @Route("/space/{spaceId}/complete/{roleId}", name="user_complete", requirements={"spaceId"="\d+", "roleId"="\d+"})
     *
     * @param $spaceId
     * @param $roleId
     * @param UserService $userService
     * @param Request $request
     * @return JsonResponse
     */
    public function completeInvitationAction($spaceId, $roleId, UserService $userService, Request $request)
    {
        try {
            $this->normalizeJson($request);

            $userService->completeInvitation(
                $spaceId,
                $roleId,
                [
                    'firstName'  => $request->get('firstName'),
                    'lastName'   => $request->get('lastName'),
                    'password'   => $request->get('password'),
                    'rePassword' => $request->get('rePassword'),
                    'token'      => $request->get('token'),
                    'email'      => $request->get('email'),
                ]
            );

            $response = $this->respondSuccess(
                'Invitation successfully accepted',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

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
            $userService->resetPassword($id);
            $response = $this->respondSuccess(
                'Password recovery link sent, please check email.',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}