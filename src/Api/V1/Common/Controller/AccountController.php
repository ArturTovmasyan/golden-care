<?php

namespace App\Api\V1\Common\Controller;

use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\AccountService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/v1.0/account")
 *
 * Class AccountController
 * @package App\Api\V1\Common\Controller
 */
class AccountController extends BaseController
{
    /**
     * @Route("/signup", name="api_account_signup", methods={"POST"})
     *
     * @param Request $request
     * @param AccountService $userService
     * @return JsonResponse
     */
    public function signupAction(Request $request, AccountService $userService): JsonResponse
    {
        $userService->signup(
            [
                'organization' => $request->get('organization'),
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'email' => $request->get('email'),
                'password' => $request->get('password'),
                're_password' => $request->get('re_password'),
                'phone' => $request->get('phone')
            ],
            $request->getSchemeAndHttpHost()
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/forgot-password", name="api_account_forgot_password", methods={"POST"})
     *
     * @param Request $request
     * @param AccountService $userService
     * @return JsonResponse
     */
    public function forgotPasswordAction(Request $request, AccountService $userService): JsonResponse
    {
        $userService->forgotPassword(
            $request->get('email'),
            $request->getSchemeAndHttpHost()
        );

        return $this->respondSuccess(
            ResponseCode::RECOVERY_LINK_SENT_TO_EMAIL
        );
    }

    /**
     * @Route("/reset-password", name="api_account_reset_password", methods={"PUT"})
     *
     * @param Request $request
     * @param AccountService $userService
     * @return JsonResponse
     */
    public function resetPasswordAction(Request $request, AccountService $userService): JsonResponse
    {
        $userService->resetPassword(
            [
                'hash' => $request->get('hash'),
                'password' => $request->get('password'),
                're_password' => $request->get('re_password')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/activate", name="api_account_activate", methods={"PUT"})
     *
     * @param Request $request
     * @param AccountService $userService
     * @return JsonResponse
     */
    public function activateAction(Request $request, AccountService $userService): JsonResponse
    {
        $userService->activate(
            [
                'hash' => $request->get('hash'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /*********** Review user invite functional ************/

    /**
     * @Route("/accept", name="api_account_accept", methods={"POST"})
     *
     * @param Request $request
     * @param AccountService $userService
     * @return JsonResponse
     */
    public function acceptInvitationAction(Request $request, AccountService $userService): JsonResponse
    {
        $userService->acceptInvitation(
            $request->get('token'),
            [
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'password' => $request->get('password'),
                're_password' => $request->get('re_password'),
                'phone' => $request->get('phone')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
}