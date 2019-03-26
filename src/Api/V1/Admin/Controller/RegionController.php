<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\RegionService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Region;
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
 * @Route("/api/v1.0/admin/region")
 *
 * @Grant(grant="persistence-region", level="VIEW")
 *
 * Class RegionController
 * @package App\Api\V1\Admin\Controller
 */
class RegionController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/region/grid Get Regions Grid
     * @apiVersion 1.0.0
     * @apiName Get Regions Grid
     * @apiGroup Admin Region
     * @apiDescription This function is used to listing regions
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the region
     * @apiSuccess {String}  name            The name of the region
     * @apiSuccess {String}  description     The description time of the region
     * @apiSuccess {String}  shorthand       The shorthand time of the region
     * @apiSuccess {String}  phone           The phone time of the region
     * @apiSuccess {String}  fax             The fax time of the region
     * @apiSuccess {Object}  space           The space of the region
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": "1",
     *          "per_page": 10,
     *          "all_pages": 1,
     *          "total": 5,
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "name": "Central Valley",
     *                  "description": "Lodi, et al",
     *                  "shorthand": "CV",
     *                  "phone": "(916) 444-4444",
     *                  "fax": "(916) 444-5555",
     *                  "space": "alms"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_region_grid", methods={"GET"})
     *
     * @param Request $request
     * @param RegionService $regionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, RegionService $regionService)
    {
        return $this->respondGrid(
            $request,
            Region::class,
            'api_admin_region_grid',
            $regionService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/region/grid Get Region Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Region Grid Options
     * @apiGroup Admin Region
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the region listing
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
     * @Route("/grid", name="api_admin_region_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Region::class, 'api_admin_region_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/region Get Regions
     * @apiVersion 1.0.0
     * @apiName Get Regions
     * @apiGroup Admin Region
     * @apiDescription This function is used to listing regions
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the region
     * @apiSuccess {String}  name            The name of the region
     * @apiSuccess {String}  description     The description time of the region
     * @apiSuccess {String}  shorthand       The shorthand time of the region
     * @apiSuccess {String}  phone           The phone time of the region
     * @apiSuccess {String}  fax             The fax time of the region
     * @apiSuccess {Object}  space           The space of the region
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "page": "1",
     *          "per_page": 10,
     *          "all_pages": 1,
     *          "total": 5,
     *          "data": [
     *              {
     *                  "id": 1,
     *                  "name": "Central Valley",
     *                  "description": "Lodi, et al",
     *                  "shorthand": "CV",
     *                  "phone": "(916) 444-4444",
     *                  "fax": "(916) 444-4444",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_region_list", methods={"GET"})
     *
     * @param Request $request
     * @param RegionService $regionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, RegionService $regionService)
    {
        return $this->respondList(
            $request,
            Region::class,
            'api_admin_region_list',
            $regionService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/region/{id} Get Region
     * @apiVersion 1.0.0
     * @apiName Get Region
     * @apiGroup Admin Region
     * @apiDescription This function is used to get region
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the region
     * @apiSuccess {String}  name            The name of the region
     * @apiSuccess {String}  description     The description time of the region
     * @apiSuccess {String}  shorthand       The shorthand time of the region
     * @apiSuccess {String}  phone           The phone time of the region
     * @apiSuccess {String}  fax             The fax time of the region
     * @apiSuccess {Object}  space           The space of the region
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "name": "Central Valley",
     *                  "description": "Lodi, et al",
     *                  "shorthand": "CV",
     *                  "phone": "(916) 444-4444",
     *                  "fax": "(916) 444-5555",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_region_get", methods={"GET"})
     *
     * @param RegionService $regionService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, RegionService $regionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $regionService->getById($id),
            ['api_admin_region_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/region Add Region
     * @apiVersion 1.0.0
     * @apiName Add Region
     * @apiGroup Admin Region
     * @apiDescription This function is used to add region
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name            The name of the region
     * @apiParam {String}  [description]   The description of the region
     * @apiParam {String}  shorthand       The shorthand of the region
     * @apiParam {String}  [phone]         The phone of the region
     * @apiParam {String}  [fax]           The fax of the region
     * @apiParam {Int}     space_id        The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "name": "Central Valley",
     *          "description": "Lodi, et al",
     *          "shorthand": "CV",
     *          "phone": "(916) 444-4444",
     *          "fax": "(916) 444-5555",
     *          "space_id": 1
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
     * @Route("", name="api_admin_region_add", methods={"POST"})
     *
     * @Grant(grant="persistence-region", level="ADD")
     *
     * @param Request $request
     * @param RegionService $regionService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, RegionService $regionService)
    {
        $id = $regionService->add(
            [
                'name' => $request->get('name'),
                'description' => $request->get('description') ?? '',
                'shorthand' => $request->get('shorthand'),
                'phone' => $request->get('phone') ?? '',
                'fax' => $request->get('fax') ?? '',
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/region/{id} Edit Region
     * @apiVersion 1.0.0
     * @apiName Edit Region
     * @apiGroup Admin Region
     * @apiDescription This function is used to edit region
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name            The name of the region
     * @apiParam {String}  [description]   The description of the region
     * @apiParam {String}  shorthand       The shorthand of the region
     * @apiParam {String}  [phone]         The phone of the region
     * @apiParam {String}  [fax]           The fax of the region
     * @apiParam {Int}     space_id        The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "name": "Central Valley",
     *          "description": "Lodi, et al",
     *          "shorthand": "CV",
     *          "phone": "(916) 444-4444",
     *          "fax": "(916) 444-5555",
     *          "space_id": 1
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_region_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-region", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param RegionService $regionService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, RegionService $regionService)
    {
        $regionService->edit(
            $id,
            [
                'name' => $request->get('name'),
                'description' => $request->get('description') ?? '',
                'shorthand' => $request->get('shorthand'),
                'phone' => $request->get('phone') ?? '',
                'fax' => $request->get('fax') ?? '',
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/region/{id} Delete Region
     * @apiVersion 1.0.0
     * @apiName Delete Region
     * @apiGroup Admin Region
     * @apiDescription This function is used to remove region
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
     *          "code": 624,
     *          "error": "Region not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_region_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-region", level="DELETE")
     *
     * @param $id
     * @param RegionService $regionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, RegionService $regionService)
    {
        $regionService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/region Bulk Delete Regions
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Regions
     * @apiGroup Admin Region
     * @apiDescription This function is used to bulk remove regions
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the regions
     *
     * @apiParamExample {json} Request-Example:
     *     ["2", "1", "5"]
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 624,
     *          "error": "Region not found"
     *     }
     *
     * @Route("", name="api_admin_region_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-region", level="DELETE")
     *
     * @param Request $request
     * @param RegionService $regionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, RegionService $regionService)
    {
        $regionService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/region/related/info Region related info
     * @apiVersion 1.0.0
     * @apiName Region Related Info
     * @apiGroup Admin Regions
     * @apiDescription This function is used to get region related info
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the facilities
     *
     * @apiParamExample {json} Request-Example:
     *     ["2", "1", "5"]
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 624,
     *          "error": "Region not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_region_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param RegionService $regionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, RegionService $regionService)
    {
        $relatedData = $regionService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
