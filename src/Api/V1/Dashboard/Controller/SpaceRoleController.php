<?php
namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Common\Controller\BaseController;
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
     *          "data": {
     *              "roles": [
     *                  {
     *                      "id": 1,
     *                      "name": "Administrator",
     *                      "default": false,
     *                      "space_default": true
     *                  }
     *              ]
     *          }
     *     }
     *
     * @Route("", name="api_dashboard_space_role_list", requirements={"spaceId"="\d+"}, methods={"GET"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        try {
            $space = $request->get('space');
            $roles = $this->em->getRepository(Role::class)->findRolesBySpace($space);

            $response = $this->respondSuccess(
                Response::HTTP_OK,
                '',
                ['roles' => $roles],
                ['api_dashboard_space_role_list']
            );
        } catch (\Throwable $e) {
            $response = $this->respondError($e->getMessage(), $e->getCode());
        }

        return $response;
    }
}