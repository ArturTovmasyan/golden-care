<?php
namespace App\Api\V1\Controller\Rest;

use App\Api\V1\Service\Exception\ValidationException;
use App\Api\V1\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;

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
 * Class SecurityController
 * @package App\Api\V1\Controller\Rest
 *
 * @Route("/api/v1.0/security")
 */
class SecurityController extends BaseController
{
    /**
     * @api {post} /api/v1.0/security/signup Sign Up
     * @apiVersion 1.0.0
     * @apiName Sign Up
     * @apiGroup User
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
     *          "error": "Validation error",
     *          "details": [
     *              {
     *                  "email": "Sorry, this email address is already in use."
     *              }
     *          ]
     *     }
     *
     * @Route("/signup", name="security_signup", methods={"POST"})
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
                '',
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode(), $e->getErrors());
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @api {put} /api/v1.0/security/change-password Change Password
     * @apiVersion 1.0.0
     * @apiName Change Password
     * @apiGroup User
     * @apiPermission none
     * @apiDescription This function is used to change password
     *
     * @apiHeader  {String} Content-Type  application/json
     * @apiHeader  {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String} password         The old password of the user
     * @apiParam {String} new_password     The new password of the user
     * @apiParam {String} re_new_password  The confirmation of the user password
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "password": "OLD_PASSWORD",
     *         "new_password": "NEW_PASSWORD",
     *         "re_new_password": "NEW_PASSWORD"
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "error": "New password must be different from last password"
     *     }
     *
     * @Route("/change-password", name="security_change_password", methods={"PUT"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function changePasswordAction(Request $request, UserService $userService)
    {
        try {
            $this->normalizeJson($request);

            $userService->changePassword(
                $this->get('security.token_storage')->getToken()->getUser(),
                [
                    'password'        => $request->get('password'),
                    'new_password'    => $request->get('new_password'),
                    're_new_password' => $request->get('re_new_password')
                ]
            );
            $response = $this->respondSuccess(
                '',
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode(), $e->getErrors());
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @api {post} /api/v1.0/security/forgot-password Forgot Password
     * @apiVersion 1.0.0
     * @apiName Forgot Password
     * @apiGroup User
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
     *          "message": "Password recovery link sent, please check email."
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "error": "User by email test@example.com not found"
     *     }
     *
     * @Route("/forgot-password", name="security_forgot_password", methods={"POST"})
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
                'Password recovery link sent, please check email.',
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode(), $e->getErrors());
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @api {put} /api/v1.0/security/confirm-password Confirm Password
     * @apiVersion 1.0.0
     * @apiName Confirm Password
     * @apiGroup User
     * @apiPermission none
     * @apiDescription This function is used to confirm password with hash
     *
     * @apiHeader {String} Content-Type  application/json
     *
     * @apiParam {String} hash         The email address of the user
     * @apiParam {String} new_password The new password of the user
     * @apiParam {String} re_password  The confirmation of the user password
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "hash": "ddasdsadft%453543543",
     *         "new_password": "NEW_PASSWORD",
     *         "re_password": "NEW_PASSWORD"
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {
     *          "message": "New password is confirmed."
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "error": "User by hash ddasdsadft%453543543 not found"
     *     }
     *
     * @Route("/confirm-password", name="security_confirm_password", methods={"PUT"})
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
                    'hash'         => $request->get('hash'),
                    'new_password' => $request->get('new_password'),
                    're_password'  => $request->get('re_password')
                ]
            );
            $response = $this->respondSuccess(
                'New password is confirmed.',
                Response::HTTP_CREATED
            );
        } catch (ValidationException $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode(), $e->getErrors());
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}