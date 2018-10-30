<?php
namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Dashboard\Service\UserService;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Common\Model\ResponseCode;
use App\Entity\User;
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
 * @Route("/api/v1.0/dashboard/user")
 *
 * Class AccountController
 * @package App\Api\V1\Dashboard\Controller
 */
class UserController extends BaseController
{
    /**
     * @api {get} /api/v1.0/dashboard/user/me My Profile
     * @apiVersion 1.0.0
     * @apiName My Profile
     * @apiGroup Dashboard User
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
     * @Route("/me", name="api_dashboard_user_me", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function getAction()
    {
        try {
            $user = $this->get('security.token_storage')->getToken()->getUser();

            $response = $this->respondSuccess(
                Response::HTTP_OK,
                '',
                $user,
                ['api_dashboard_space_user_get']
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @api {post} /api/v1.0/dashboard/user/edit Edit Profile
     * @apiVersion 1.0.0
     * @apiName Edit Profile
     * @apiGroup Dashboard User
     * @apiPermission none
     * @apiDescription This function is used to edit user profile
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  first_name        The First Name of the user
     * @apiParam {String}  last_name         The Last Name of the user
     * @apiParam {String}  phone             The phone number of the user
     * @apiParam {Boolean} enabled           The enabled status of the user
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "first_name": "Joe",
     *         "last_name": "Cole",
     *         "phone": "+37400000000",
     *         "enabled": 1
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
     * @Route("/edit", name="api_dashboard_user_edit", methods={"POST"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function editAction(Request $request, UserService $userService)
    {
        try {
            $this->normalizeJson($request);
            $userService->editUser(
                $this->get('security.token_storage')->getToken()->getUser(),
                [
                    'first_name'  => $request->get('first_name'),
                    'last_name'   => $request->get('last_name'),
                    'phone'       => $request->get('phone'),
                    'enabled'     => $request->get('enabled'),
                ]
            );

            $response = $this->respondSuccess(
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
     * @api {put} /api/v1.0/dashboard/user/change-password Change Password
     * @apiVersion 1.0.0
     * @apiName Change Password
     * @apiGroup Dashboard User
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
     *          "code": 619
     *          "error": "New password must be different from last password"
     *     }
     *
     * @Route("/change-password", name="api_dashboard_user_change_password", methods={"PUT"})
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
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}