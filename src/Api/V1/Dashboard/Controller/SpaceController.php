<?php
namespace App\Api\V1\Dashboard\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Dashboard\Service\SpaceRoleService;
use App\Api\V1\Dashboard\Service\RoleService;
use App\Api\V1\Dashboard\Service\SpaceService;
use App\Entity\Role;
use App\Entity\Space;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\Permission;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

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
 * @Route("/api/v1.0/dashboard/space/{spaceId}")
 * @Permission({"PERMISSION_SPACE"})
 *
 * Class SpaceController
 * @package App\Api\V1\Dashboard\Controller
 */
class SpaceController extends BaseController
{
    /**
     * @api {get} /api/v1.0/dashboard/space/{space_id} Get Space
     * @apiVersion 1.0.0
     * @apiName Get Space
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_SPACE
     * @apiDescription This function is used to get space information
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int} space_id The unique identifier of the space
     *
     * @apiSuccess {Int}     id            The unique identifier of the space
     * @apiSuccess {String}  name          The Name of the space
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *               "id": 1,
     *               "name": "ALMS"
     *          }
     *     }
     *
     * @Route("", name="api_dashboard_space_get", requirements={"spaceId"="\d+"}, methods={"GET"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $request->get('space'),
            ['api_dashboard_space_get']
        );
    }

    /**
     * @api {post} /api/v1.0/dashboard/space/{space_id} Edit Space
     * @apiVersion 1.0.0
     * @apiName Edit Space
     * @apiGroup Dashboard Space
     * @apiPermission PERMISSION_SPACE
     * @apiDescription This function is used to edit space
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name The name of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "name": "ALMS"
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
     * @Route("", requirements={"spaceId"="\d+"}, name="api_dashboard_space_edit", methods={"POST"})
     *
     * @param Request $request
     * @param Space $space
     * @param SpaceService $spaceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, Space $space, SpaceService $spaceService)
    {
        $spaceService->editSpace(
            $space,
            [
                'name' => $request->get('name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
}