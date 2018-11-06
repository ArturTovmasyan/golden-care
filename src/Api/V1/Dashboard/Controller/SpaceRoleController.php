<?php
namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Dashboard\Service\RoleService;
use App\Api\V1\Dashboard\Service\SpaceRoleService;
use App\Entity\Role;
use App\Entity\Space;
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
 * @Route("/api/v1.0/dashboard/space/{spaceId}/role")
 * @Permission({"PERMISSION_ROLE"})
 *
 * Class SpaceRoleController
 * @package App\Api\V1\Dashboard\Controller
 */
class SpaceRoleController extends BaseController
{
    /**
     * @api {get} /api/v1.0/dashboard/space/{space_id}/role Get Roles
     * @apiVersion 1.0.0
     * @apiName Get Roles
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_ROLE
     * @apiDescription This function is used to listing roles by space
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} space_id      The unique identifier of the space
     *
     * @apiSuccess {Int}     id            The unique identifier of the role
     * @apiSuccess {String}  name          The Name of the role
     * @apiSuccess {Boolean} default       The status of the role
     * @apiSuccess {Boolean} space_default The main role of space for invitation
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "name": "Administrator",
     *                  "default": false,
     *                  "space_default": true
     *              }
     *          }
     *     }
     *
     * @Route("", name="api_dashboard_space_role_list", requirements={"spaceId"="\d+"}, methods={"GET"})
     *
     * @param Request $request
     * @param SpaceRoleService $spaceRoleService
     * @return JsonResponse
     */
    public function listAction(Request $request, SpaceRoleService $spaceRoleService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $spaceRoleService->getListingBySpace($request->get('space')),
            ['api_dashboard_space_role_list']
        );
    }

    /**
     * @api {options} /api/v1.0/dashboard/space/{space_id}/role Get Roles Options
     * @apiVersion 1.0.0
     * @apiName Get Roles Options
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_ROLE
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array}   options       The options of thr role listing
     * @apiSuccess {String}  total         The total count of role listing
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "options": [
     *              {
     *                  "label": "id",
     *                  "type": "integer",
     *                  "sortable": true,
     *                  "filterable": true,
     *              }
     *          ],
     *          "total": 5
     *     }
     *
     * @Route("", name="api_dashboard_space_role_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @param SpaceRoleService $spaceRoleService
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function optionAction(Request $request, SpaceRoleService $spaceRoleService)
    {
        return $this->getOptionsByGroupName(
            Role::class,
            'api_dashboard_space_role_list',
            $spaceRoleService->getTotalListingBySpace($request->get('space'))
        );
    }

    /**
     * @api {get} /api/v1.0/dashboard/space/{space_id}/role/{role_id} Get Role
     * @apiVersion 1.0.0
     * @apiName Get Role
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_ROLE
     * @apiDescription This function is used to get role by space and id
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} space_id      The unique identifier of the space
     * @apiParam {Int} role_id       The unique identifier of the role
     *
     * @apiSuccess {Int}     id            The unique identifier of the role
     * @apiSuccess {String}  name          The Name of the role
     * @apiSuccess {Boolean} default       The status of the role
     * @apiSuccess {Boolean} space_default The main role of space for invitation
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *               "id": 1,
     *               "name": "Administrator",
     *               "default": false,
     *               "space_default": true
     *          }
     *     }
     *
     * @Route("/{roleId}", name="api_dashboard_space_role_get", requirements={"spaceId"="\d+", "roleId"="\d+"}, methods={"GET"})
     *
     * @param Request $request
     * @param $roleId
     * @param SpaceRoleService $spaceRoleService
     * @return JsonResponse
     */
    public function getAction(Request $request, $roleId, SpaceRoleService $spaceRoleService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $spaceRoleService->getBySpaceAndId($request->get('space'), $roleId),
            ['api_dashboard_space_role_get']
        );
    }

    /**
     * @api {post} /api/v1.0/dashboard/space/{space_id}/role Add Role
     * @apiVersion 1.0.0
     * @apiName Add Role
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_ROLE
     * @apiDescription This function is used to add role for space
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name          The name of the role
     * @apiParam {Int}     space_default The space status of the role
     * @apiParam {Array}   permissions[] The parameter ids
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "name": "User Management",
     *         "space_default": 1,
     *         "permissions": [1, 2]
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
     * @Route("", name="api_dashboard_role_add", methods={"POST"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, RoleService $roleService)
    {
        $roleService->addRole(
            $request->get('space'),
            [
                'name'          => $request->get('name'),
                'space_default' => $request->get('space_default'),
                'permissions'   => $request->get('permissions')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {post} /api/v1.0/dashboard/space/{space_id}/role/{id} Edit Role
     * @apiVersion 1.0.0
     * @apiName Edit Role
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_ROLE
     * @apiDescription This function is used to edit role for space
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     id            The unique identifier of the role
     * @apiParam {String}  name          The name of the role
     * @apiParam {Boolean} space_default The space status of the role
     * @apiParam {Array}   permissions[] The parameter ids
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "name": "User Management",
     *         "space_default": false,
     *         "permissions": [1, 2]
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
     * @Route("/{id}", requirements={"spaceId"="\d+", "id"="\d+"}, name="api_dashboard_role_edit", methods={"POST"})
     *
     * @param Request $request
     * @param $id
     * @param Space $space
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, Space $space, RoleService $roleService)
    {
        $roleService->editRole(
            $id,
            $space,
            [
                'name'          => $request->get('name'),
                'space_default' => $request->get('space_default'),
                'permissions'   => $request->get('permissions')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/dashboard/space/{space_id}/role/{id} Delete Role
     * @apiVersion 1.0.0
     * @apiName Delete Role
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_ROLE
     * @apiDescription This function is used to remove role
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} id The unique identifier of the role
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 611,
     *          "error": "Role not found"
     *     }
     *
     * @Route("/{id}", requirements={"spaceId"="\d+", "id"="\d+"}, name="api_dashboard_role_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param Space $space
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeAction(Request $request, $id, Space $space, RoleService $roleService)
    {
        $roleService->removeRole($id, $space);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}