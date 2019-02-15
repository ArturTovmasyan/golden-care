<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Admin\Service\RoleService;
use App\Api\V1\Common\Service\GrantService;
use App\Entity\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use App\Annotation\Grant as Grant;

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
 * @Grant(grant="persistence-security-role", level="VIEW")
 *
 * Class RoleController
 * @package App\Api\V1\Admin\Controller
 */
class RoleController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/role/grid Get Roles Grid
     * @apiVersion 1.0.0
     * @apiName Get Roles Grid
     * @apiGroup Admin Role
     * @apiDescription This function is used to roles grid
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
     *          "total": 5,
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "name": "Administrator",
     *                  "space": "Space name",
     *                  "default": false,
     *                  "space_default": false
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_role_grid", methods={"GET"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, RoleService $roleService)
    {
        return $this->respondGrid(
            $request,
            Role::class,
            'api_admin_role_grid',
            $roleService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/role/grid Get Roles Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Roles Grid Options
     * @apiGroup Admin Role
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of thr role listing
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          [
     *              {
     *                  "id": "name",
     *                  "type": "integer",
     *                  "sortable": true,
     *                  "filterable": true,
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_role_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Role::class, 'api_admin_role_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/role/grid Get Roles Grid
     * @apiVersion 1.0.0
     * @apiName Get Roles Grid
     * @apiGroup Admin Role
     * @apiDescription This function is used to roles grid
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the role
     * @apiSuccess {String}  name          The Name of the role
     * @apiSuccess {Object}  space         The Space of the role
     * @apiSuccess {Boolean} default       The status of the role
     * @apiSuccess {Boolean} space_default The space status of the role
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          [
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
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function listAction(Request $request, RoleService $roleService)
    {
        return $this->respondList(
            $request,
            Role::class,
            'api_admin_role_list',
            $roleService
        );
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
     *          "id": 1,
     *          "name": "Administrator",
     *          "default": false,
     *          "space_default": false
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_role_get", methods={"GET"})
     *
     * @param Request $request
     * @param RoleService $roleService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, RoleService $roleService, GrantService $grantService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $roleService->getById($id, $grantService),
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
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "name": "User Management",
     *         "space_id": 1,
     *         "default": true,
     *         "space_default": 0,
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
     * @Grant(grant="persistence-security-role", level="ADD")
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function addAction(Request $request, RoleService $roleService)
    {
        $roleService->add(
            [
                'name'          => $request->get('name'),
                'grants'        => $request->get('grants'),
                'default'       => $request->get('default')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/role/{id} Edit Role
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_role_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-security-role", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function editAction(Request $request, $id, RoleService $roleService)
    {
        $roleService->edit(
            $id,
            [
                'name'          => $request->get('name'),
                'grants'        => $request->get('grants'),
                'default'       => $request->get('default')
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
     * @Grant(grant="persistence-security-role", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, RoleService $roleService)
    {
        $roleService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/role Bulk Delete Roles
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Roles
     * @apiGroup Admin Role
     * @apiDescription This function is used to bulk remove roles
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Array} ids The unique identifier of the role
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
     * @Route("", name="api_admin_role_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-role", level="DELETE")
     *
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, RoleService $roleService)
    {
        $roleService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
