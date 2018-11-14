<?php

namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Admin\Service\PermissionService;
use App\Entity\Permission;
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
 * @Route("/api/v1.0/admin/permission")
 *
 * Class PermissionController
 * @package App\Api\V1\Admin\Controller
 */
class PermissionController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/permission/grid Get Permissions Grid
     * @apiVersion 1.0.0
     * @apiName Get Permissions Grid
     * @apiGroup Admin Role
     * @apiPermission none
     * @apiDescription This function is used to get user all permissions
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id    The identifier of the user
     * @apiSuccess {String}  name  The name of the permission
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": 1,
     *          "total": 2,
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "name": "PERMISSION_ROLE"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_permission_grid", methods={"GET"})
     *
     * @param Request $request
     * @param PermissionService $permissionService
     * @return \Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse|JsonResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, PermissionService $permissionService)
    {
        return $this->respondGrid(
            $request,
            Permission::class,
            'api_admin_permission_grid',
            $permissionService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/permission/grid Get Permissions Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Permissions Grid Options
     * @apiGroup Admin Role
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array}   options The options of the permissions listing
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
     * @Route("/grid", name="api_admin_permission_grid_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Permission::class, 'api_admin_permission_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/permission Get Permissions
     * @apiVersion 1.0.0
     * @apiName Get Permissions
     * @apiGroup Admin Role
     * @apiPermission none
     * @apiDescription This function is used to get user all permissions
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id    The identifier of the user
     * @apiSuccess {String}  name  The name of the permission
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          [
     *              {
     *                  "id": 1,
     *                  "name": "PERMISSION_ROLE"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_permission_list", methods={"GET"})
     *
     * @param Request $request
     * @param PermissionService $permissionService
     * @return \Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse|JsonResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, PermissionService $permissionService)
    {
        return $this->respondList(
            $request,
            Permission::class,
            'api_admin_permission_list',
            $permissionService
        );
    }
}
