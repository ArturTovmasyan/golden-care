<?php
namespace App\Api\V1\Controller\Rest;

use App\Api\V1\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Service\UserService;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\Permission;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class UserController
 * @package App\Api\V1\Controller\Rest
 * @Route("/api/v1.0")
 */
class UserController extends BaseController
{
    /**
     * @Method("GET")
     * @Route("/space/{spaceId}/user", name="user_list", requirements={"spaceId"="\d+"})
     * @Permission({"PERMISSION_USER"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        try {
            $space = $request->get('space');

            if (is_null($space)) {
                throw new SpaceNotFoundException(Response::HTTP_BAD_REQUEST);
            }

            $users = $this->em->getRepository(User::class)->findUsersBySpace($space);

            $response = $this->respondSuccess(
                '',
                Response::HTTP_OK,
                ['users' => $users],
                ['api_space__user_list']
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @Method("GET")
     * @Route("/user/{id}", name="user_info", requirements={"id"="\d+"})
     * @Permission({"PERMISSION_USER"})
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
     * @Permission({"PERMISSION_USER"})
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
     * This function is used to accept space invitation
     *
     * @Method("POST")
     * @Route("/space/{spaceId}/accept", name="user_accept", requirements={"spaceId"="\d+"})
     * @Permission({"PERMISSION_USER"})
     *
     * @param $spaceId
     * @param UserService $userService
     * @return JsonResponse
     */
    public function acceptInvitationAction($spaceId, UserService $userService)
    {
        try {
            $userService->acceptInvitation($spaceId);

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
     * This function is used to reset space invitation
     *
     * @Method("POST")
     * @Route("/space/{spaceId}/reject", name="user_reject", requirements={"spaceId"="\d+"})
     * @Permission({"PERMISSION_USER"})
     *
     * @param $spaceId
     * @param UserService $userService
     * @return JsonResponse
     */
    public function rejectInvitationAction($spaceId, UserService $userService)
    {
        try {
            $userService->rejectInvitation($spaceId);

            $response = $this->respondSuccess(
                'Invitation successfully rejected',
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
     * @Route("/space/{spaceId}/complete", name="user_complete", requirements={"spaceId"="\d+"})
     *
     * @param $spaceId
     * @param UserService $userService
     * @param Request $request
     * @return JsonResponse
     */
    public function completeInvitationAction($spaceId, UserService $userService, Request $request)
    {
        try {
            $this->normalizeJson($request);

            $userService->completeInvitation(
                $spaceId,
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
     * @Permission({"PERMISSION_USER"})
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