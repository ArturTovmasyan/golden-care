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
 * @Route("/api/v1.0/admin/space")
 *
 * @Grant(grant="persistence-security-space", level="VIEW")
 *
 * Class SpaceController
 * @package App\Api\V1\Admin\Controller
 */
class SpaceController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/space/grid Get Spaces Grid
     * @apiVersion 1.0.0
     * @apiName Get Spaces Grid
     * @apiGroup Admin Space
     * @apiDescription This function is used to listing spaces
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the space
     * @apiSuccess {String}  name          The Name of the space
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
     *                  "name": "ALMS"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_space_grid", methods={"GET"})
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, SpaceService $spaceService)
    {
        return $this->respondGrid(
            $request,
            Space::class,
            'api_admin_space_grid',
            $spaceService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/space Get Spaces Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Spaces Grid Options
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
     * @Route("/grid", name="api_admin_space_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Space::class, 'api_admin_space_grid');
    }

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
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          [
     *              {
     *                  "id": 1,
     *                  "name": "ALMS"
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
        return $this->respondList(
            $request,
            Space::class,
            'api_admin_space_list',
            $spaceService
        );
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
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "id": 1,
     *          "name": "ALMS"
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
     * @api {post} /api/v1.0/admin/space Add Space
     * @apiVersion 1.0.0
     * @apiName Add Space
     * @apiGroup Admin Space
     * @apiDescription This function is used to add Space
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name      The name of the Space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "name": "ALMS"
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
     *              "name": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_space_add", methods={"POST"})
     *
     * @Grant(grant="persistence-security-space", level="ADD")
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, SpaceService $spaceService)
    {
        $spaceService->add(
            [
                'name' => $request->get('name')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/space/{id} Edit Space
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_space_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-security-space", level="EDIT")
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

    /**
     * @api {delete} /api/v1.0/admin/space/{id} Delete Space
     * @apiVersion 1.0.0
     * @apiName Delete Space
     * @apiGroup Admin Space
     * @apiDescription This function is used to remove Space
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 612,
     *          "error": "Space not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_space_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-space", level="DELETE")
     *
     * @param $id
     * @param SpaceService $spaceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, SpaceService $spaceService)
    {
        $spaceService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/space Bulk Delete Spaces
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Spaces
     * @apiGroup Admin Space
     * @apiDescription This function is used to bulk remove spaces
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the spaces
     *
     * @apiParamExample {json} Request-Example:
     *     ["1", "4", "5"]
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 612,
     *          "error": "Space not found"
     *     }
     *
     * @Route("", name="api_admin_space_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-security-space", level="DELETE")
     *
     * @param Request $request
     * @param SpaceService $spaceService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, SpaceService $spaceService)
    {
        $spaceService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
