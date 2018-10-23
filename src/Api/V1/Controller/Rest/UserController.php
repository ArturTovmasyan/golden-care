<?php
namespace App\Api\V1\Controller\Rest;

use App\Api\V1\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Service\Exception\ValidationException;
use App\Api\V1\Service\UserService;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
 * Class UserController
 * @package App\Api\V1\Controller\Rest
 * @Route("/api/v1.0")
 */
class UserController extends BaseController
{
    /**
     * @api {get} /api/v1.0/space/{spaceId}/user Space Users
     * @apiVersion 1.0.0
     * @apiName Space Users
     * @apiGroup Space
     * @apiPermission PERMISSION_USER
     * @apiDescription This function is used to listing users by space
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
     *              "users": [
     *                  {
     *                      "id": 1,
     *                      "first_name": "Joe",
     *                      "last_name": "Cole",
     *                      "username": "joe",
     *                      "email": "joe.cole@gmail.com",
     *                      "enabled": true,
     *                      "completed": true,
     *                      "last_activity_at": "2018-10-22T17:31:48+04:00"
     *                  }
     *              ]
     *          }
     *     }
     *
     * @Route("/space/{spaceId}/user", name="user_list", requirements={"spaceId"="\d+"}, methods={"GET"})
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
     * @api {get} /api/v1.0/user/{id} Get User
     * @apiVersion 1.0.0
     * @apiName Get Users
     * @apiGroup User
     * @apiPermission PERMISSION_USER
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
     *          "data": {
     *              "users": [
     *                  {
     *                      "id": 1,
     *                      "first_name": "Joe",
     *                      "last_name": "Cole",
     *                      "username": "joe",
     *                      "email": "joe.cole@gmail.com",
     *                      "enabled": true,
     *                      "completed": true,
     *                      "last_activity_at": "2018-10-22T17:31:48+04:00"
     *                  }
     *              ]
     *          }
     *     }
     *
     * @Route("/user/{id}", name="user_info", requirements={"id"="\d+"}, methods={"GET"})
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
     * @api {put} /api/v1.0/user Add User
     * @apiVersion 1.0.0
     * @apiName Add User
     * @apiGroup User
     * @apiPermission PERMISSION_USER
     * @apiDescription This function is used to add user for admin
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  first_name        The First Name of the user
     * @apiParam {String}  last_name         The Last Name of the user
     * @apiParam {String}  username          The username of the user
     * @apiParam {String}  email             The email address of the user
     * @apiParam {String}  password          The password of the user
     * @apiParam {String}  re_password       The repeat password of the user
     * @apiParam {String}  phone             The phone number of the user
     * @apiParam {Int}     enabled           The enabled status of the user
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "first_name": "Joe",
     *         "last_name": "Cole",
     *         "username": "joe-cole",
     *         "email": "test@example.com",
     *         "password": "PASSWORD",
     *         "re_password": "PASSWORD",
     *         "phone": "+37400000000",
     *         "enabled": 1
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
     * @Route("/user", name="user_add", methods={"PUT"})
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function addAction(Request $request, UserService $userService)
    {
        try {
            $this->normalizeJson($request);
            $userService->addUser(
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
     * @api {post} /api/v1.0/user/{id} Edit User
     * @apiVersion 1.0.0
     * @apiName Edit User
     * @apiGroup User
     * @apiPermission PERMISSION_USER
     * @apiDescription This function is used to edit user for admin
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     id                The unque identifier of the user
     * @apiParam {String}  first_name        The First Name of the user
     * @apiParam {String}  last_name         The Last Name of the user
     * @apiParam {String}  phone             The phone number of the user
     * @apiParam {Int}     enabled           The enabled status of the user
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "first_name": "Joe",
     *         "last_name": "Cole",
     *         "phone": "+37400000000",
     *         "enabled": 1
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
     * @Route("/user/{id}", name="user_edit", requirements={"id"="\d+"}, methods={"POST"})
     *
     * @param $id
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function editAction($id, Request $request, UserService $userService)
    {
        try {
            $this->normalizeJson($request);
            $userService->editUser(
                $id,
                [
                    'first_name'  => $request->get('first_name'),
                    'last_name'   => $request->get('last_name'),
                    'phone'       => $request->get('phone'),
                    'enabled'     => $request->get('enabled'),
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
     * @api {post} /api/v1.0/space/{space_id}/user/invite Invite User
     * @apiVersion 1.0.0
     * @apiName Invite User
     * @apiGroup Space
     * @apiPermission PERMISSION_USER
     * @apiDescription This function is used for user invitation to space
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
     *         "message": "Invitation sent to email address, please check email."
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "message": "User already joined to team"
     *     }
     *
     * @Route("/space/{spaceId}/user/invite", name="user_invite", requirements={"spaceId"="\d+"}, methods={"POST"})
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
                'Invitation sent to email address, please check email.',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @api {post} /api/v1.0/space/{space_id}/accept Accept Invitation
     * @apiVersion 1.0.0
     * @apiName Accept Invitation
     * @apiGroup Space
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
     *     {
     *         "message": "Invitation successfully accepted."
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "message": "Invalid user access for space"
     *     }
     *
     *
     * @Route("/space/{spaceId}/accept", name="user_accept", requirements={"spaceId"="\d+"}, methods={"POST"})
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
     * @api {post} /api/v1.0/space/{space_id}/reject Reject Invitation
     * @apiVersion 1.0.0
     * @apiName Reject Invitation
     * @apiGroup Space
     * @apiPermission PERMISSION_USER
     * @apiDescription This function is used to reject space invitation
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} space_id The unique identifier for space
     *
     * @apiParamExample {json} Request-Example:
     *     {}
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {
     *         "message": "Invitation successfully rejected."
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "message": "Invalid user access for space"
     *     }
     *
     * @Route("/space/{spaceId}/reject", name="user_reject", requirements={"spaceId"="\d+"}, methods={"POST"})
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
     * @api {post} /api/v1.0/space/{space_id}/complete Complete Invitation
     * @apiVersion 1.0.0
     * @apiName Complete Invitation
     * @apiGroup Space
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
     *     {
     *         "message": "Invitation successfully accepted."
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "message": "Space Not found"
     *     }
     *
     * @Route("/space/{spaceId}/complete", name="user_complete", requirements={"spaceId"="\d+"}, methods={"POST"})
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
                    'firstName'  => $request->get('first_ame'),
                    'lastName'   => $request->get('last_name'),
                    'password'   => $request->get('password'),
                    'rePassword' => $request->get('re_password'),
                    'token'      => $request->get('token'),
                    'email'      => $request->get('email'),
                ]
            );

            $response = $this->respondSuccess(
                'Invitation successfully accepted.',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @api {post} /api/v1.0/user/reset-password/{id} Reset User Password
     * @apiVersion 1.0.0
     * @apiName Reset User Password
     * @apiGroup Admin
     * @apiPermission PERMISSION_USER
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
     *         "message": "Password recovery link sent, please check email."
     *     }
     *
     * @Route("/user/reset-password/{id}", name="user_reset_password", requirements={"id"="\d+"}, methods={"PUT"})
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