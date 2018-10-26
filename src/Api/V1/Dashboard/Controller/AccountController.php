<?php
namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Dashboard\Service\UserService;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Model\ResponseCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
 * @Route("/api/v1.0/dashboard/account")
 *
 * Class AccountController
 * @package App\Api\V1\Dashboard\Controller
 */
class AccountController extends BaseController
{
    /**
     * @api {post} /api/v1.0/dashboard/account/signup Sign Up
     * @apiVersion 1.0.0
     * @apiName Sign Up
     * @apiGroup Dashboard Account
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
     * @Route("/signup", name="api_dashboard_account_signup", methods={"POST"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function signupAction(Request $request, UserService $userService)
    {
        try {
            $this->normalizeJson($request);
            $userService->signup(
                [
                    'first_name'  => $request->get('first_name'),
                    'last_name'   => $request->get('last_name'),
                    'email'       => $request->get('email'),
                    'password'    => $request->get('password'),
                    're_password' => $request->get('re_password')
                ]
            );
            $response = $this->respondSuccess(
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @api {post} /api/v1.0/dashboard/account/forgot-password Forgot Password
     * @apiVersion 1.0.0
     * @apiName Forgot Password
     * @apiGroup Dashboard Account
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
     * @Route("/forgot-password", name="api_dashboard_account_forgot_password", methods={"POST"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function forgotPasswordAction(Request $request, UserService $userService)
    {
        try {
            $this->normalizeJson($request);
            $userService->forgotPassword(
                $request->get('email'),
                $request->getSchemeAndHttpHost()
            );

            $response = $this->respondSuccess(
                ResponseCode::RECOVERY_LINK_SENT_TO_EMAIL
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @api {put} /api/v1.0/dashboard/account/confirm-password Confirm Password
     * @apiVersion 1.0.0
     * @apiName Confirm Password
     * @apiGroup Dashboard Account
     * @apiPermission none
     * @apiDescription This function is used to confirm password with hash
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
     * @Route("/confirm-password", name="api_dashboard_account_forgot_password_confirm_password", methods={"PUT"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function confirmPasswordAction(Request $request, UserService $userService)
    {
        try {
            $this->normalizeJson($request);
            $userService->confirmPassword(
                [
                    'hash'        => $request->get('hash'),
                    'password'    => $request->get('password'),
                    're_password' => $request->get('re_password')
                ]
            );
            $response = $this->respondSuccess(
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}