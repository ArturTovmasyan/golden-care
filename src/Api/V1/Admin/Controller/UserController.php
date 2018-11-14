<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Admin\Service\UserService;
use App\Entity\User;
use App\Api\V1\Common\Model\ResponseCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

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
 * @Route("/api/v1.0/admin/user")
 *
 * Class UserController
 * @package App\Api\V1\Admin\Controller
 */
class UserController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/user Get Users
     * @apiVersion 1.0.0
     * @apiName Get Users
     * @apiGroup Admin User
     * @apiDescription This function is used to listing users
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
     *          "page": "1",
     *          "per_page": 10,
     *          "total": 5,
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "first_name": "Joe",
     *                  "last_name": "Cole",
     *                  "username": "joe",
     *                  "email": "joe.cole@gmail.com",
     *                  "enabled": true,
     *                  "completed": true,
     *                  "last_activity_at": "2018-10-22T17:31:48+04:00"
     *              }
     *          }
     *     }
     *
     * @Route("", name="api_admin_user_list", methods={"GET"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, UserService $userService)
    {
        return $this->respondGrid(
            $request,
            User::class,
            'api_admin_user_list',
            $userService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/user Get Users Options
     * @apiVersion 1.0.0
     * @apiName Get Users Options
     * @apiGroup Admin User
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array}   options The options of the user listing
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          [
     *              {
     *                  "label": "id",
     *                  "type": "integer",
     *                  "sortable": true,
     *                  "filterable": true,
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_user_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function optionAction(Request $request)
    {
        return $this->getOptionsByGroupName(User::class, 'api_admin_user_list');
    }

    /**
     * @api {get} /api/v1.0/admin/user/{id} Get User
     * @apiVersion 1.0.0
     * @apiName Get User
     * @apiGroup Admin User
     * @apiDescription This function is used to get user by identifier
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam   {Int}     id                The identifier of the user
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
     *          "id": 1,
     *          "first_name": "Joe",
     *          "last_name": "Cole",
     *          "username": "joe",
     *          "email": "joe.cole@gmail.com",
     *          "enabled": true,
     *          "completed": true,
     *          "last_activity_at": "2018-10-22T17:31:48+04:00"
     *     }
     *
     * @Route("/{id}", name="api_admin_user_get", requirements={"id"="\d+"}, methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param UserService $userService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, UserService $userService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $userService->getById($id),
            ['api_admin_user_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/user Add User
     * @apiVersion 1.0.0
     * @apiName Add User
     * @apiGroup Admin User
     * @apiDescription This function is used to add user
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  first_name        The First Name of the user
     * @apiParam {String}  last_name         The Last Name of the user
     * @apiParam {String}  username          The unique username of the user
     * @apiParam {String}  email             The unique email of the user
     * @apiParam {String}  password          The password of the user
     * @apiParam {String}  re_password       The password confirmation of the user
     * @apiParam {String}  phone             The phone number of the user
     * @apiParam {Boolean} enabled           The enabled status of the user
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "first_name": "Joe",
     *         "last_name": "Cole",
     *         "username": "joe-cole",
     *         "email": "joe@gmail.com",
     *         "password": "PASSWORD",
     *         "re_password": "PASSWORD",
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
     *              "username": "Sorry, this username is already taken."
     *          }
     *     }
     *
     * @Route("", name="api_admin_user_add", methods={"POST"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, UserService $userService)
    {
        $userService->add(
            [
                'first_name'  => $request->get('first_name'),
                'last_name'   => $request->get('last_name'),
                'username'    => $request->get('username'),
                'email'       => $request->get('email'),
                'password'    => $request->get('password'),
                're_password' => $request->get('re_password'),
                'phone'       => $request->get('phone'),
                'enabled'     => $request->get('enabled'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {post} /api/v1.0/admin/user/{id} Edit User
     * @apiVersion 1.0.0
     * @apiName Edit User
     * @apiGroup Admin User
     * @apiDescription This function is used to edit user
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     id                The unique identifier of the user
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
     *              "name": "Sorry, this name is already in use."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_user_edit", methods={"POST"})
     *
     * @param Request $request
     * @param $id
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, UserService $userService)
    {
        $userService->edit(
            $id,
            [
                'first_name'  => $request->get('first_name'),
                'last_name'   => $request->get('last_name'),
                'phone'       => $request->get('phone'),
                'enabled'     => $request->get('enabled'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/user/{id}/reset-password Reset Password
     * @apiVersion 1.0.0
     * @apiName Reset Password
     * @apiGroup Admin User
     * @apiDescription This function is used to reset user password
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} id The unique identifier of the user
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {
     *         "code": 230,
     *         "message": "Password recovery link sent, please check email"
     *     }
     *
     * @Route("/{id}/reset-password", requirements={"id"="\d+"}, name="api_admin_user_reset_password", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Exception
     */
    public function resetPasswordAction(Request $request, $id, UserService $userService)
    {
        $userService->resetPassword($id);

        return $this->respondSuccess(
            ResponseCode::RECOVERY_LINK_SENT_TO_EMAIL
        );
    }
}