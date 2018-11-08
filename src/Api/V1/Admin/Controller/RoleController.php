<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Admin\Service\RoleService;
use App\Entity\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
 * @Route("/api/v1.0/admin/role")
 *
 * Class RoleController
 * @package App\Api\V1\Admin\Controller
 */
class RoleController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/role Get Roles
     * @apiVersion 1.0.0
     * @apiName Get Roles
     * @apiGroup Admin Role
     * @apiDescription This function is used to listing roles
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the role
     * @apiSuccess {String}  name          The Name of the role
     * @apiSuccess {Boolean} default       The status of the role
     * @apiSuccess {Boolean} space_default The space status of the role
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": "1",
     *          "per_page": 10,
     *          "all_pages": 1,
     *          "total": 5,
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "name": "Administrator",
     *                  "space": {
     *                      "id": 1
     *                  },
     *                  "default": false,
     *                  "space_default": false
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_role_list", methods={"GET"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, RoleService $roleService)
    {
        $queryBuilder = $this->getQueryBuilder($request, Role::class, 'api_admin_role_list');

        return $this->respondPagination(
            $request,
            $queryBuilder,
            $roleService->getListing($queryBuilder),
            ['api_admin_role_list']
        );
    }

    /**
     * @api {options} /api/v1.0/admin/role Get Roles Options
     * @apiVersion 1.0.0
     * @apiName Get Roles Options
     * @apiGroup Admin Role
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
     *                  "id": "name",
     *                  "type": "integer",
     *                  "sortable": true,
     *                  "filterable": true,
     *              }
     *          ],
     *          "total": 5
     *     }
     *
     * @Route("", name="api_admin_role_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function optionAction(Request $request, RoleService $roleService)
    {
        return $this->getOptionsByGroupName(Role::class, 'api_admin_role_list', $roleService->getListingCount());
    }

    /**
     * @api {get} /api/v1.0/admin/role/{id} Get Role
     * @apiVersion 1.0.0
     * @apiName Get Role
     * @apiGroup Admin Role
     * @apiDescription This function is used to get role
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the role
     * @apiSuccess {String}  name          The Name of the role
     * @apiSuccess {Boolean} default       The status of the role
     * @apiSuccess {Boolean} space_default The space status of the role
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *              "id": 1,
     *              "name": "Administrator",
     *              "default": false,
     *              "space_default": false,
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_role_get", methods={"GET"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, RoleService $roleService, $id)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $roleService->getById($id),
            ['api_admin_role_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/role Add Role
     * @apiVersion 1.0.0
     * @apiName Add Role
     * @apiGroup Admin Role
     * @apiDescription This function is used to add role
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name          The name of the role
     * @apiParam {Int}     space_id      The unique identifier of the space
     * @apiParam {Int}     default       The global status of the role
     * @apiParam {Int}     space_default The space status of the role
     * @apiParam {Array}   permissions[] The parameter ids
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "name": "User Management",
     *         "space_id": 1,
     *         "default": true,
     *         "space_default": 0,
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
     * @Route("", name="api_admin_role_add", methods={"POST"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, RoleService $roleService)
    {
        $roleService->addRole(
            [
                'name'          => $request->get('name'),
                'space_id'      => $request->get('space_id'),
                'default'       => $request->get('default'),
                'space_default' => $request->get('space_default'),
                'permissions'   => $request->get('permissions')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {post} /api/v1.0/admin/role/{id} Edit Role
     * @apiVersion 1.0.0
     * @apiName Edit Role
     * @apiGroup Admin Role
     * @apiDescription This function is used to edit role
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     id            The unique identifier of the role
     * @apiParam {String}  name          The name of the role
     * @apiParam {Int}     space_id      The unique identifier of the space
     * @apiParam {Int}     default       The global status of the role
     * @apiParam {Boolean} space_default The space status of the role
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "name": "User Management",
     *         "space_id": 1,
     *         "default": true,
     *         "space_default": false,
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_role_edit", methods={"POST"})
     *
     * @param Request $request
     * @param $id
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, RoleService $roleService)
    {
        $roleService->editRole(
            $id,
            [
                'name'          => $request->get('name'),
                'space_id'      => $request->get('space_id'),
                'default'       => $request->get('default'),
                'space_default' => $request->get('space_default'),
                'permissions'   => $request->get('permissions')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/role/{id} Delete Role
     * @apiVersion 1.0.0
     * @apiName Delete Role
     * @apiGroup Admin Role
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_role_delete", methods={"DELETE"})
     *
     * @param Request $request
     * @param $id
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function removeAction(Request $request, $id, RoleService $roleService)
    {
        $roleService->removeRole($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}