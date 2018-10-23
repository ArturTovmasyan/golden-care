<?php
namespace App\Api\V1\Controller\Rest;

use App\Api\V1\Service\Exception\RoleNotFoundException;
use App\Api\V1\Service\Exception\SpaceNotFoundException;
use App\Api\V1\Service\Exception\UserNotFoundException;
use App\Api\V1\Service\RoleService;
use App\Entity\Role;
use App\Entity\Space;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
 * Class UserController
 * @package App\Api\V1\Controller\Rest
 * @Route("/api/v1.0")
 * @Permission({"PERMISSION_ROLE"})
 */
class RoleController extends BaseController
{
    /**
     * @api {get} /api/v1.0/space/{space_id}/role Space Roles
     * @apiVersion 1.0.0
     * @apiName Space Roles
     * @apiGroup Space
     * @apiPermission PERMISSION_ROLE
     * @apiDescription This function is used to listing roles by space
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id      The unique identifier of the role
     * @apiSuccess {String}  name    The Name of the role
     * @apiSuccess {Boolean} default The status of the role
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
                    "roles": [
                        {
                            "id": 1,
                            "name": "Administrator",
                            "default": false
                        }
                    ]
                }
     *     }
     *
     * @Route("/space/{spaceId}/role", name="space_role_list", requirements={"spaceId"="\d+"}, methods={"GET"})
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

            $roles = $this->em->getRepository(Role::class)->findRolesBySpace($space);

            $response = $this->respondSuccess(
                '',
                Response::HTTP_OK,
                ['roles' => $roles],
                ['api_space__role_list']
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @api {post} /api/v1.0/space/{space_id}/role/{role_id}/user/{user_id} Add Role
     * @apiVersion 1.0.0
     * @apiName Add Role
     * @apiGroup Space
     * @apiPermission PERMISSION_ROLE
     * @apiDescription This function is used to add space role to user
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}  space_id        The unique identifier of the space
     * @apiParam {Int}  role_id         The unique identifier of the role
     * @apiParam {Int}  user_id         The unique identifier of the user
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 201 Created
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "error": "Invalid user access for space"
     *     }
     *
     * @Route("/space/{spaceId}/role/{roleId}/user/{userId}", name="space_add_role_to_user", requirements={"spaceId"="\d+", "roleId"="\d+", "userId"="\d+"}, methods={"POST"})
     *
     * @param $roleId
     * @param $userId
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function addAction($roleId, $userId, Request $request, RoleService $roleService)
    {
        try {
            /**
             * @var Space $space
             * @var Role $role
             * @var User $user
             */
            $space = $request->get('space');
            $role  = $this->em->getRepository(Role::class)->find($roleId);
            $user  = $this->em->getRepository(User::class)->find($userId);

            if (is_null($space)) {
                throw new SpaceNotFoundException(Response::HTTP_BAD_REQUEST);
            }

            if (is_null($role)) {
                throw new RoleNotFoundException(Response::HTTP_BAD_REQUEST);
            }

            if (is_null($user)) {
                throw new UserNotFoundException('User not found', Response::HTTP_BAD_REQUEST);
            }

            $roleService->addRole($space, $role, $user);

            $response = $this->respondSuccess(
                '',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }

    /**
     * @api {delete} /api/v1.0/space/{space_id}/role/{role_id}/user/{user_id} Delete Role
     * @apiVersion 1.0.0
     * @apiName Delete Role
     * @apiGroup Space
     * @apiPermission PERMISSION_ROLE
     * @apiDescription This function is used to remove role from space
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}  space_id        The unique identifier of the space
     * @apiParam {Int}  role_id         The unique identifier of the role
     * @apiParam {Int}  user_id         The unique identifier of the user
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "error": "Invalid user access for space"
     *     }
     *
     * @Route("/space/{spaceId}/role/{roleId}/user/{userId}", name="space_delete_role_from_user", requirements={"spaceId"="\d+", "roleId"="\d+", "userId"="\d+"}, methods={"DELETE"})
     *
     * @param $roleId
     * @param $userId
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function removeAction($roleId, $userId, Request $request, RoleService $roleService)
    {
        try {
            /**
             * @var Space $space
             * @var Role $role
             * @var User $user
             */
            $space = $request->get('space');
            $role  = $this->em->getRepository(Role::class)->find($roleId);
            $user  = $this->em->getRepository(User::class)->find($userId);

            if (is_null($space)) {
                throw new SpaceNotFoundException(Response::HTTP_BAD_REQUEST);
            }

            if (is_null($role)) {
                throw new RoleNotFoundException(Response::HTTP_BAD_REQUEST);
            }

            if (is_null($user)) {
                throw new UserNotFoundException('User not found', Response::HTTP_BAD_REQUEST);
            }

            $roleService->removeRole($space, $role, $user);

            $response = $this->respondSuccess(
                '',
                Response::HTTP_NO_CONTENT
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}