<?php
namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Dashboard\Service\UserService;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\Exception\SpaceNotFoundException;
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
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        try {
            $space = $request->get('space');

            if (is_null($space)) {
                throw new SpaceNotFoundException();
            }

            $users = $this->em->getRepository(User::class)->findUsersBySpace($space);

            $response = $this->respondSuccess(
                Response::HTTP_OK,
                '',
                $users,
                ['api_dashboard_space_user_list']
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
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
     *          "data": {
     *              "id": 1,
     *              "first_name": "Joe",
     *              "last_name": "Cole",
     *              "username": "joe",
     *              "email": "joe.cole@gmail.com",
     *              "enabled": true,
     *              "completed": true,
     *              "last_activity_at": "2018-10-22T17:31:48+04:00"
     *          }
     *     }
     *
     * @Route("/{id}", name="api_dashboard_space_user_get", requirements={"spaceId"="\d+", "id"="\d+"}, methods={"GET"})
     * @Permission({"PERMISSION_USER"})
     *
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getAction($id, Request $request)
    {
        try {
            $space = $request->get('space');

            if (is_null($space)) {
                throw new SpaceNotFoundException();
            }

            $user = $this->em->getRepository(User::class)->findUserBySpace($space, $id);

            $response = $this->respondSuccess(
                Response::HTTP_OK,
                '',
                ['user' => $user],
                ['api_dashboard_space_user_get']
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
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
                $request->get('role_id')
            );

            $response = $this->respondSuccess(
                ResponseCode::INVITATION_LINK_SENT_TO_EMAIL
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
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
     * @param $spaceId
     * @param UserService $userService
     * @return JsonResponse
     */
    public function acceptInvitationAction($spaceId, UserService $userService)
    {
        try {
            $userService->acceptInvitation($spaceId);

            $response = $this->respondSuccess(
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
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
     */
    public function rejectInvitationAction($spaceId, UserService $userService)
    {
        try {
            $userService->rejectInvitation($spaceId);

            $response = $this->respondSuccess(
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
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
                    'first_name'  => $request->get('first_ame'),
                    'last_name'   => $request->get('last_name'),
                    'password'    => $request->get('password'),
                    're_password' => $request->get('re_password'),
                    'token'       => $request->get('token'),
                    'email'       => $request->get('email'),
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