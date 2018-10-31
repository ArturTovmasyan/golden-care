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
        try {
            $response = $this->respondSuccess(
                Response::HTTP_OK,
                '',
                $spaceRoleService->getListingBySpace($request->get('space')),
                ['api_dashboard_space_role_list']
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
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
     */
    public function addAction(Request $request, RoleService $roleService)
    {
        try {
            $this->normalizeJson($request);

            $space = $request->get('space');

            $roleService->addRole(
                $space,
                [
                    'name'          => $request->get('name'),
                    'space_default' => $request->get('space_default'),
                    'permissions'   => $request->get('permissions')
                ]
            );

            $response = $this->respondSuccess(
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
     * @param $id
     * @param Space $space
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function editAction($id, Space $space, Request $request, RoleService $roleService)
    {
        try {
            $this->normalizeJson($request);
            $roleService->editRole(
                $id,
                $space,
                [
                    'name'          => $request->get('name'),
                    'space_default' => $request->get('space_default'),
                    'permissions'   => $request->get('permissions')
                ]
            );

            $response = $this->respondSuccess(
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
     * @param $id
     * @param Space $space
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function removeAction($id, Space $space, RoleService $roleService)
    {
        try {
            $roleService->removeRole($id, $space);

            $response = $this->respondSuccess(
                Response::HTTP_NO_CONTENT
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}