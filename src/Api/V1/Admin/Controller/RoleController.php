<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\Exception\ValidationException;
use App\Api\V1\Admin\Service\RoleService;
use App\Entity\Role;
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
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "name": "Administrator",
     *                  "default": false,
     *                  "space_default": false,
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_role_list", methods={"GET"})
     * @return JsonResponse
     */
    public function listAction()
    {
        try {
            $roles = $this->em->getRepository(Role::class)->findAll();

            $response = $this->respondSuccess(
                Response::HTTP_OK,
                '',
                $roles,
                ['api_admin_role_list']
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
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
     */
    public function addAction(Request $request, RoleService $roleService)
    {
        try {
            $this->normalizeJson($request);
            $roleService->addRole(
                [
                    'name'          => $request->get('name'),
                    'space_id'      => $request->get('space_id'),
                    'default'       => $request->get('default'),
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
     * @param $id
     * @param Request $request
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function editAction($id, Request $request, RoleService $roleService)
    {
        try {
            $this->normalizeJson($request);
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
     * @param $id
     * @param RoleService $roleService
     * @return JsonResponse
     */
    public function removeAction($id, RoleService $roleService)
    {
        try {
            $roleService->removeRole($id);

            $response = $this->respondSuccess(
                Response::HTTP_NO_CONTENT
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}