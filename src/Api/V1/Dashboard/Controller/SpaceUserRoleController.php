<?php
namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Dashboard\Service\SpaceUserRoleService;
use App\Api\V1\Common\Controller\BaseController;
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
 * @Route("/api/v1.0/dashboard/space/{spaceId}/user/{userId}/role/{roleId}")
 * @Permission({"PERMISSION_ROLE"})
 *
 * Class SpaceUserRoleController
 * @package App\Api\V1\Dashboard\Controller
 */
class SpaceUserRoleController extends BaseController
{
    /**
     * @api {put} /api/v1.0/dashboard/space/{space_id}/user/{user_id}/role/{role_id} Change User Role
     * @apiVersion 1.0.0
     * @apiName Change User Role
     * @apiGroup Dashboard Space
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
     *          "code": 616,
     *          "error": "Invalid user access for space"
     *     }
     *
     * @Route("", name="api_dashboard_space_user_role_change", requirements={"spaceId"="\d+", "userId"="\d+", "roleId"="\d+"}, methods={"PUT"})
     *
     * @param Request $request
     * @param $roleId
     * @param $userId
     * @param SpaceUserRoleService $spaceUserRoleService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function changeAction(Request $request, $roleId, $userId, SpaceUserRoleService $spaceUserRoleService)
    {
        $spaceUserRoleService->changeRole($request->get('space'), $userId, $roleId);

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
}