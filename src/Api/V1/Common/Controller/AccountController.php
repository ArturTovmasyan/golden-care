<?php
namespace App\Api\V1\Common\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\AccountService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\Permission;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @IgnoreAnnotation("api")
 * @IgnoreAnnotation("apiVersion")
 * @IgnoreAnnotation("apiName")
 * @IgnoreAnnotation("apiGroup")
 * @IgnoreAnnotation("apiDescription")
 * @IgnoreAnnotation("apiHeader")
 * @IgnoreAnnotation("apiSuccess")
 * @IgnoreAnnotation("apiSuccessExample")
 * @IgnoreAnnotation("apiParam")
 * @IgnoreAnnotation("apiParamExample")
 * @IgnoreAnnotation("apiErrorExample")
 * @IgnoreAnnotation("apiPermission")
 *
 * @Route("/api/v1.0/account")
 *
 * Class AccountController
 * @package App\Api\V1\Common\Controller
 */
class AccountController extends BaseController
{
    /**
     * @api {post} /api/v1.0/account/signup Sign Up
     * @apiVersion 1.0.0
     * @apiName Sign Up
     * @apiGroup Common Account
     * @apiPermission none
     * @apiDescription This function is used to signup user
     *
     * @apiHeader {String} Content-Type  application/json
     *
     * @apiParam {String}  first_name        The First Name of the user
     * @apiParam {String}  last_name         The Last Name of the user
     * @apiParam {String}  email             The email address of the user
     * @apiParam {String}  password          The password of the user
     * @apiParam {String}  re_password       The repeat password of the user
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "first_name": "Joe",
     *         "last_name": "Cole",
     *         "email": "test@example.com",
     *         "password": "PASSWORD",
     *         "re_password": "PASSWORD"
     *     }
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 618,
     *          "error": "User with this email address or username already exist"
     *     }
     *
     * @Route("/signup", name="api_account_signup", methods={"POST"})
     *
     * @param Request $request
     * @param AccountService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function signupAction(Request $request, AccountService $userService)
    {
        $userService->signup(
            [
                'organization' => $request->get('organization'),
                'first_name'   => $request->get('first_name'),
                'last_name'    => $request->get('last_name'),
                'email'        => $request->get('email'),
                'password'     => $request->get('password'),
                're_password'  => $request->get('re_password'),
                'phone'        => $request->get('phone')
            ],
            $request->getSchemeAndHttpHost()
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {post} /api/v1.0/account/forgot-password Forgot Password
     * @apiVersion 1.0.0
     * @apiName Forgot Password
     * @apiGroup Common Account
     * @apiPermission none
     * @apiDescription This function is used to forgot password
     *
     * @apiHeader {String} Content-Type  application/json
     *
     * @apiParam {String} email The email address of the user
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "email": "test@example.com"
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {
     *         "code": 230,
     *         "message": "Password recovery link sent, please check email"
     *     }
     *
     * @Route("/forgot-password", name="api_account_forgot_password", methods={"POST"})
     *
     * @param Request $request
     * @param AccountService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function forgotPasswordAction(Request $request, AccountService $userService)
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
     * @api {put} /api/v1.0/account/reset-password Reset Password
     * @apiVersion 1.0.0
     * @apiName Reset Password
     * @apiGroup Common Account
     * @apiPermission none
     * @apiDescription This function is used to reset password with hash
     *
     * @apiHeader {String} Content-Type  application/json
     *
     * @apiParam {String} hash         The email address of the user
     * @apiParam {String} password     The new password of the user
     * @apiParam {String} re_password  The confirmation of the user password
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "hash": "ddasdsadft%453543543",
     *         "password": "NEW_PASSWORD",
     *         "re_password": "NEW_PASSWORD"
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 613,
     *          "error": "User not found"
     *     }
     *
     * @Route("/reset-password", name="api_account_reset_password", methods={"PUT"})
     *
     * @param Request $request
     * @param AccountService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function resetPasswordAction(Request $request, AccountService $userService)
    {
        $userService->resetPassword(
            [
                'hash'        => $request->get('hash'),
                'password'    => $request->get('password'),
                're_password' => $request->get('re_password')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/account/activate Activate
     * @apiVersion 1.0.0
     * @apiName Activate
     * @apiGroup Common Account
     * @apiPermission none
     * @apiDescription This function is used to activate user with hash
     *
     * @apiHeader {String} Content-Type  application/json
     *
     * @apiParam {String} hash         The email address of the user
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "hash": "ddasdsadft%453543543",
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 613,
     *          "error": "User not found"
     *     }
     *
     * @Route("/activate", name="api_account_activate", methods={"PUT"})
     *
     * @param Request $request
     * @param AccountService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function activateAction(Request $request, AccountService $userService)
    {
        $userService->activate(
            [
                'hash'        => $request->get('hash'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /*********** Review user invite functional ************/
    /**
     * @api {post} /api/v1.0/dashboard/space/{space_id}/user/invite Invite User
     * @apiVersion 1.0.0
     * @apiName Invite User
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_USER
     * @apiDescription This function is used for user invitation to space
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}    space_id     The unique identifier for space
     * @apiParam {String} email        The unique email of the user
     * @apiParam {Int}    role_id      The role of the identifier
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "email": "test@example.com",
     *         "role_id": 1
     *     }
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {
     *         "code": 231,
     *         "message": "Invitation sent to email address, please check email"
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 614,
     *          "message": "User already joined to space"
     *     }
     *
     * @Route("/invite", name="api_dashboard_space_user_invite", requirements={"spaceId"="\d+"}, methods={"POST"})
     * @Permission({"PERMISSION_USER"})
     *
     * @param Request $request
     * @param $spaceId
     * @param AccountService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function inviteAction(Request $request, $spaceId, AccountService $userService)
    {
        $userService->invite(
            $spaceId,
            $request->get('email'),
            $request->get('role_id')
        );
        return $this->respondSuccess(
            ResponseCode::INVITATION_LINK_SENT_TO_EMAIL
        );
    }
    /**
     * @api {post} /api/v1.0/dashboard/space/{space_id}/user/accept Accept Invitation
     * @apiVersion 1.0.0
     * @apiName Accept Invitation
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_USER
     * @apiDescription This function is used to accept space invitation for registered users
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} space_id The unique identifier for space
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 614,
     *          "message": "User already joined to space"
     *     }
     *
     *
     * @Route("/accept", name="api_dashboard_space_user_accept", requirements={"spaceId"="\d+"}, methods={"POST"})
     * @Permission({"PERMISSION_USER"})
     *
     * @param Request $request
     * @param $spaceId
     * @param AccountService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function acceptInvitationAction(Request $request, $spaceId, AccountService $userService)
    {
        $userService->acceptInvitation($spaceId);
        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
    /**
     * @api {post} /api/v1.0/dashboard/space/{space_id}/user/reject Reject Invitation
     * @apiVersion 1.0.0
     * @apiName Reject Invitation
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_USER
     * @apiDescription This function is used to reject space invitation
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} space_id The unique identifier for space
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 614,
     *          "message": "User already joined to space"
     *     }
     *
     * @Route("/reject", name="api_dashboard_space_user_accept", requirements={"spaceId"="\d+"}, methods={"POST"})
     * @Permission({"PERMISSION_USER"})
     *
     * @param $spaceId
     * @param AccountService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function rejectInvitationAction(Request $request, $spaceId, AccountService $userService)
    {
        $userService->rejectInvitation($spaceId);
        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
    /**
     * @api {post} /api/v1.0/dashboard/space/{space_id}/user/complete Complete Invitation
     * @apiVersion 1.0.0
     * @apiName Complete Invitation
     * @apiGroup Dashboard Space
     * @apiPermission none
     * @apiDescription This function is used to complete profile and accept invitation
     *
     * @apiHeader {String} Content-Type  application/json
     *
     * @apiParam {Int}     space_id          The unique identifier for space
     * @apiParam {String}  first_name        The First Name of the user
     * @apiParam {String}  last_name         The Last Name of the user
     * @apiParam {String}  email             The email address of the user
     * @apiParam {String}  password          The password of the user
     * @apiParam {String}  re_password       The repeat password of the user
     * @apiParam {String}  token             The email requested token of the user
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "first_name": "Joe",
     *         "last_name": "Cole",
     *         "password": "PASSWORD",
     *         "re_password": "PASSWORD",
     *         "token": "TOKEN",
     *         "email": "test@example.com"
     *     }
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 614,
     *          "message": "User already joined to space"
     *     }
     *
     * @Route("/complete", name="api_dashboard_space_user_complete", requirements={"spaceId"="\d+"}, methods={"POST"})
     *
     * @param Request $request
     * @param $spaceId
     * @param AccountService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function completeInvitationAction(Request $request, $spaceId, AccountService $userService)
    {
        $userService->completeInvitation(
            $spaceId,
            [
                'first_name'  => $request->get('first_ame'),
                'last_name'   => $request->get('last_name'),
                'password'    => $request->get('password'),
                're_password' => $request->get('re_password'),
                'token'       => $request->get('token'),
                'email'       => $request->get('email'),
            ]
        );
        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
}
