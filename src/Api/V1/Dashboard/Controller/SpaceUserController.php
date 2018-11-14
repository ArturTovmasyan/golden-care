<?php
namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Dashboard\Service\SpaceUserService;
use App\Api\V1\Dashboard\Service\UserService;
use App\Entity\User;
use App\Api\V1\Common\Model\ResponseCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\Permission;
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
 * @Route("/api/v1.0/dashboard/space/{spaceId}/user")
 *
 * Class SpaceUserController
 * @package App\Api\V1\Dashboard\Controller
 */
class SpaceUserController extends BaseController
{
    /**
     * @api {get} /api/v1.0/dashboard/space/{space_id}/user Get Users
     * @apiVersion 1.0.0
     * @apiName Get Users
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_USER
     * @apiDescription This function is used to listing users by space
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam   {Int}     space_id          The identifier of the space
     *
     * @apiSuccess {Int}     id                The identifier of the user
     * @apiSuccess {String}  first_name        The First Name of the user
     * @apiSuccess {String}  last_name         The Last Name of the user
     * @apiSuccess {String}  username          The unique username of the user
     * @apiSuccess {String}  email             The unique email of the user
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
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 401,
     *          "error": "Permission denied for this resource"
     *     }
     *
     * @Route("", name="api_dashboard_space_user_list", requirements={"spaceId"="\d+"}, methods={"GET"})
     * @Permission({"PERMISSION_USER"})
     *
     * @param Request $request
     * @param SpaceUserService $spaceUserService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, SpaceUserService $spaceUserService)
    {
        return $this->respondGrid(
            $request,
            User::class,
            'api_dashboard_space_user_list',
            $spaceUserService,
            $request->get('space')
        );
    }

    /**
     * @api {options} /api/v1.0/dashboard/space/{space_id}/role Get Users Options
     * @apiVersion 1.0.0
     * @apiName Get Users Options
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_USER
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array}   options       The options of thr user listing
     * @apiSuccess {String}  total         The total count of user listing
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
     * @Route("", name="api_dashboard_space_user_options", requirements={"spaceId"="\d+"}, methods={"OPTIONS"})
     * @Permission({"PERMISSION_USER"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function optionAction(Request $request)
    {
        return $this->getOptionsByGroupName(User::class, 'api_dashboard_space_user_list');
    }

    /**
     * @api {get} /api/v1.0/dashboard/space/{space_id}/user/{id} Get User
     * @apiVersion 1.0.0
     * @apiName Get User
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_USER
     * @apiDescription This function is used to get space related user by identifier
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam   {Int}     space_id          The identifier of the space
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
     *          "space_user_roles": [
     *              {
     *                  "space": {
     *                      "id": 1,
     *                      "name": "First"
     *                  },
     *                  "role": {
     *                      "id": 1,
     *                      "name": "Admin Management",
     *                      "permissions": [
     *                          {
     *                              "id": 1,
     *                              "name": "PERMISSION_ROLE"
     *                          },
     *                          {
     *                              "id": 2,
     *                               "name": "PERMISSION_USER"
     *                          }
     *                      ]
     *                  }
     *              }
     *          ]
     *     }
     *
     * @Route("/{id}", name="api_dashboard_space_user_get", requirements={"spaceId"="\d+", "id"="\d+"}, methods={"GET"})
     * @Permission({"PERMISSION_USER"})
     *
     * @param Request $request
     * @param $id
     * @param SpaceUserService $spaceUserService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, SpaceUserService $spaceUserService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $spaceUserService->getBySpaceAndId($request->get('space'), $id),
            ['api_dashboard_space_user_get']
        );
    }

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
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function inviteAction(Request $request, $spaceId, UserService $userService)
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
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function acceptInvitationAction(Request $request, $spaceId, UserService $userService)
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
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function rejectInvitationAction(Request $request, $spaceId, UserService $userService)
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
     * @param UserService $userService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function completeInvitationAction(Request $request, $spaceId, UserService $userService)
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