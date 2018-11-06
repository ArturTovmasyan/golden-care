<?php

namespace App\Api\V1\Common\Controller;

use App\Api\V1\Common\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
 * @Route("/api/v1.0/common/user")
 *
 * Class ProfileController
 * @package App\Api\V1\Common\Controller
 */
class ProfileController extends BaseController
{
    /**
     * @api {get} /api/v1.0/common/user/me My Profile
     * @apiVersion 1.0.0
     * @apiName My Profile
     * @apiGroup Common
     * @apiPermission none
     * @apiDescription This function is used to get user profile
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id                The identifier of the user
     * @apiSuccess {String}  first_name        The First Name of the user
     * @apiSuccess {String}  last_name         The Last Name of the user
     * @apiSuccess {String}  email             The email of the user
     * @apiSuccess {Boolean} enabled           The enabled status of the user
     * @apiSuccess {Boolean} completed         The profile completed status of the user
     * @apiSuccess {String}  last_activity_at  The last activity date of the user
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *               "id": 1,
     *               "first_name": "Joe",
     *               "last_name": "Cole",
     *               "username": "joe",
     *               "email": "joe.cole@gmail.com",
     *               "enabled": true,
     *               "completed": true,
     *               "last_activity_at": "2018-10-22T17:31:48+04:00"
     *          }
     *     }
     *
     * @Route("/me", name="api_common_user_me", methods={"GET"})
     *
     * @var Request $request
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $this->get('security.token_storage')->getToken()->getUser(),
            ['api_common_user_me']
        );
    }

    /**
     * @api {post} /api/v1.0/common/user/edit Edit Profile
     * @apiVersion 1.0.0
     * @apiName Edit Profile
     * @apiGroup Common
     * @apiPermission none
     * @apiDescription This function is used to edit user profile
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  first_name        The First Name of the user
     * @apiParam {String}  last_name         The Last Name of the user
     * @apiParam {String}  phone             The phone number of the user
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "first_name": "Joe",
     *         "last_name": "Cole",
     *         "phone": "+37400000000"
     *     }
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 610,
     *          "error": "Validation error",
     *          "details": {
     *              "first_name": "This value should not be blank."
     *          }
     *     }
     *
     * @Route("/edit", name="api_common_user_edit", methods={"POST"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, UserService $userService)
    {
        $userService->editUser(
            $this->get('security.token_storage')->getToken()->getUser(),
            [
                'first_name'  => $request->get('first_name'),
                'last_name'   => $request->get('last_name'),
                'phone'       => $request->get('phone')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/common/user/change-password Change Password
     * @apiVersion 1.0.0
     * @apiName Change Password
     * @apiGroup Common
     * @apiPermission none
     * @apiDescription This function is used to change password
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
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
     *          "code": 619,
     *          "error": "Validation error",
     *          "details": {
     *              "confirmPassword": "This value should be equal to password",
     *              "plainPassword": "This value should not be equal to old password"
     *          }
     *     }
     *
     * @Route("/change-password", name="api_common_user_change_password", methods={"PUT"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function changePasswordAction(Request $request, UserService $userService)
    {
        $userService->changePassword(
            $this->get('security.token_storage')->getToken()->getUser(),
            [
                'password'        => $request->get('password'),
                'new_password'    => $request->get('new_password'),
                're_new_password' => $request->get('re_new_password')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
}
