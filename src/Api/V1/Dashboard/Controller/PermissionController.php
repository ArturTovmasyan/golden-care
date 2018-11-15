<?php

namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Dashboard\Service\PermissionService;
use App\Annotation\Permission;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
 * @Route("/api/v1.0/dashboard/space/{spaceId}/permission")
 * @Permission({"PERMISSION_ROLE"})
 *
 * Class PermissionController
 * @package App\Api\V1\Dashboard\Controller
 */
class PermissionController extends BaseController
{
    /**
     * @api {get} /api/v1.0/dashboard/space/{space_id}/permission Get Permissions
     * @apiVersion 1.0.0
     * @apiName Get Permissions
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_ROLE
     * @apiDescription This function is used to get user all permissions for dashboard
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id                The identifier of the user
     * @apiSuccess {String}  name              The name of the permission
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
     * @Route("", name="api_dashboard_permission_list", requirements={"spaceId"="\d+"}, methods={"GET"})
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
            \App\Entity\Permission::class,
            'api_dashboard_permission_list',
            $permissionService,
            $request->get('space')
        );
    }
}
