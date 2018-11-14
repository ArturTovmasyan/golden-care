<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\SpaceService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Space;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
 * @Route("/api/v1.0/admin/space")
 *
 * Class SpaceController
 * @package App\Api\V1\Admin\Controller
 */
class SpaceController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/space Get Spaces
     * @apiVersion 1.0.0
     * @apiName Get Spaces
     * @apiGroup Admin Space
     * @apiDescription This function is used to listing spaces
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the space
     * @apiSuccess {String}  name          The Name of the space
     * @apiSuccess {Boolean} created       The creation date of the space
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
     *                  "name": "ALMS",
     *                  "created_at": "2018-11-13T08:59:02+04:00"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_space_list", methods={"GET"})
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, SpaceService $spaceService)
    {
        return $this->respondGrid(
            $request,
            Space::class,
            'api_admin_space_list',
            $spaceService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/space Get Spaces Options
     * @apiVersion 1.0.0
     * @apiName Get Spaces Options
     * @apiGroup Admin Space
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the space listing
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
     * @Route("", name="api_admin_space_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function optionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Space::class, 'api_admin_space_list');
    }

    /**
     * @api {get} /api/v1.0/admin/space/{id} Get Space
     * @apiVersion 1.0.0
     * @apiName Get Space
     * @apiGroup Admin Space
     * @apiDescription This function is used to get space
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the space
     * @apiSuccess {String}  name          The Name of the space
     * @apiSuccess {Boolean} created_at    The creation date of the space
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "id": 1,
     *          "name": "ALMS",
     *          "created_at": "2018-11-13T08:59:02+04:00"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_space_get", methods={"GET"})
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, SpaceService $spaceService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $spaceService->getById($id),
            ['api_admin_space_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/space/{id} Edit Space
     * @apiVersion 1.0.0
     * @apiName Edit Space
     * @apiGroup Admin Space
     * @apiDescription This function is used to edit space
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String} name The name of the space
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_space_edit", methods={"POST"})
     *
     * @param Request $request
     * @param $id
     * @param SpaceService $spaceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, SpaceService $spaceService)
    {
        $spaceService->edit(
            $id,
            [
                'name' => $request->get('name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }
}