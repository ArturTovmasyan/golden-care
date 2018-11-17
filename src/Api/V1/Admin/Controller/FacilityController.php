<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\FacilityService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Facility;
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
 * @Route("/api/v1.0/admin/facility")
 *
 * Class FacilityController
 * @package App\Api\V1\Admin\Controller
 */
class FacilityController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/facility/grid Get Facilities Grid
     * @apiVersion 1.0.0
     * @apiName Get Facilities Grid
     * @apiGroup Admin Facility
     * @apiDescription This function is used to listing facilities
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the facility
     * @apiSuccess {String}  name            The name of the facility
     * @apiSuccess {String}  description     The description time of the facility
     * @apiSuccess {String}  shorthand       The shorthand time of the facility
     * @apiSuccess {String}  phone           The phone time of the facility
     * @apiSuccess {String}  fax             The fax time of the facility
     * @apiSuccess {String}  address1        The address1 time of the facility
     * @apiSuccess {String}  license         The license time of the facility
     * @apiSuccess {Object}  csz             The City State & Zip of the facility
     * @apiSuccess {Int}     max_beds_number The maxBedsNumber time of the facility
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
     *                  "name": "Citrus Heights Terrace",
     *                  "description": "Some description",
     *                  "shorthand": "CHT",
     *                  "phone": "(916) 727-4400",
     *                  "fax": "(916) 727-4232",
     *                  "address1": "7952 Old Auburn Road",
     *                  "license": "347001498",
     *                  "csz_id": 1,
     *                  "csz_city": "Verdi",
     *                  "csz_state_abbr": "CA",
     *                  "csz_zip_main": "89439",
     *                  "max_beds_number": 45
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_facility_grid", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityService $facilityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, FacilityService $facilityService)
    {
        return $this->respondGrid(
            $request,
            Facility::class,
            'api_admin_facility_grid',
            $facilityService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/facility/grid Get Facility Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Facility Grid Options
     * @apiGroup Admin Facility
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the facility listing
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
     * @Route("/grid", name="api_admin_facility_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Facility::class, 'api_admin_facility_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/facility Get Facilities
     * @apiVersion 1.0.0
     * @apiName Get Facilities
     * @apiGroup Admin Facility
     * @apiDescription This function is used to listing facilities
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the facility
     * @apiSuccess {String}  name            The name of the facility
     * @apiSuccess {String}  description     The description time of the facility
     * @apiSuccess {String}  shorthand       The shorthand time of the facility
     * @apiSuccess {String}  phone           The phone time of the facility
     * @apiSuccess {String}  fax             The fax time of the facility
     * @apiSuccess {String}  address1        The address1 time of the facility
     * @apiSuccess {String}  license         The license time of the facility
     * @apiSuccess {Object}  csz             The City State & Zip of the facility
     * @apiSuccess {Int}     max_beds_number The maxBedsNumber time of the facility
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
     *                  "name": "Citrus Heights Terrace",
     *                  "description": "Some description",
     *                  "shorthand": "CHT",
     *                  "phone": "(916) 727-4400",
     *                  "fax": "(916) 727-4232",
     *                  "address1": "7952 Old Auburn Road",
     *                  "license": "347001498",
     *                  "csz": {
     *                      "id": 1,
     *                      "state_abbr": "CA",
     *                      "zip_main": "89439",
     *                      "city": "Verdi"
     *                  },
     *                  "max_beds_number": 45
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_facility_list", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityService $facilityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, FacilityService $facilityService)
    {
        return $this->respondList(
            $request,
            Facility::class,
            'api_admin_facility_list',
            $facilityService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/facility/{id} Get Facility
     * @apiVersion 1.0.0
     * @apiName Get Facility
     * @apiGroup Admin Facility
     * @apiDescription This function is used to get facility
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the facility
     * @apiSuccess {String}  name            The name of the facility
     * @apiSuccess {String}  description     The description time of the facility
     * @apiSuccess {String}  shorthand       The shorthand time of the facility
     * @apiSuccess {String}  phone           The phone time of the facility
     * @apiSuccess {String}  fax             The fax time of the facility
     * @apiSuccess {String}  address1        The address1 time of the facility
     * @apiSuccess {String}  license         The license time of the facility
     * @apiSuccess {Object}  csz             The City State & Zip of the facility
     * @apiSuccess {Int}     max_beds_number The maxBedsNumber time of the facility
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "name": "Citrus Heights Terrace",
     *                  "description": "Some description",
     *                  "shorthand": "CHT",
     *                  "phone": "(916) 727-4400",
     *                  "fax": "(916) 727-4232",
     *                  "address1": "7952 Old Auburn Road",
     *                  "license": "347001498",
     *                  "csz": {
     *                      "id": 1,
     *                      "state_abbr": "CA",
     *                      "zip_main": "89439",
     *                      "city": "Verdi"
     *                  },
     *                  "max_beds_number": 45
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_get", methods={"GET"})
     *
     * @param FacilityService $facilityService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, FacilityService $facilityService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $facilityService->getById($id),
            ['api_admin_facility_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/facility Add Facility
     * @apiVersion 1.0.0
     * @apiName Add Facility
     * @apiGroup Admin Facility
     * @apiDescription This function is used to add facility
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name            The name of the facility
     * @apiParam {String}  [description]   The description of the facility
     * @apiParam {String}  shorthand       The shorthand of the facility
     * @apiParam {String}  [phone]         The phone of the facility
     * @apiParam {String}  [fax]           The fax of the facility
     * @apiParam {String}  address1        The address1 of the facility
     * @apiParam {String}  [license]       The license of the facility
     * @apiParam {Int}     csz_id          The unique identifier of the City State & Zip
     * @apiParam {Int}     max_beds_number The maxBedsNumber of the facility
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "name": "Citrus Heights Terrace",
     *          "description": "Some description",
     *          "shorthand": "CHT",
     *          "phone": "(916) 727-4400",
     *          "fax": "(916) 727-4232",
     *          "address1": "7952 Old Auburn Road",
     *          "license": "347001498",
     *          "csz_id": 1,
     *          "max_beds_number": 45
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
     * @Route("", name="api_admin_facility_add", methods={"POST"})
     *
     * @param Request $request
     * @param FacilityService $facilityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, FacilityService $facilityService)
    {
        $facilityService->add(
            [
                'name' => $request->get('name'),
                'description' => $request->get('description') ?? '',
                'shorthand' => $request->get('shorthand'),
                'phone' => $request->get('phone') ?? '',
                'fax' => $request->get('fax') ?? '',
                'address1' => $request->get('address1'),
                'license' => $request->get('license') ?? '',
                'csz_id' => $request->get('csz_id'),
                'max_beds_number' => $request->get('max_beds_number')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/facility/{id} Edit Facility
     * @apiVersion 1.0.0
     * @apiName Edit Facility
     * @apiGroup Admin Facility
     * @apiDescription This function is used to edit facility
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name            The name of the facility
     * @apiParam {String}  [description]   The description of the facility
     * @apiParam {String}  shorthand       The shorthand of the facility
     * @apiParam {String}  [phone]         The phone of the facility
     * @apiParam {String}  [fax]           The fax of the facility
     * @apiParam {String}  address1        The address1 of the facility
     * @apiParam {String}  [license]       The license of the facility
     * @apiParam {Int}     csz_id          The unique identifier of the City State & Zip
     * @apiParam {Int}     max_beds_number The maxBedsNumber of the facility
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "name": "Citrus Heights Terrace",
     *          "description": "Some description",
     *          "shorthand": "CHT",
     *          "phone": "(916) 727-4400",
     *          "fax": "(916) 727-4232",
     *          "address1": "7952 Old Auburn Road",
     *          "license": "347001498",
     *          "csz_id": 1,
     *          "max_beds_number": 45
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param FacilityService $facilityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, FacilityService $facilityService)
    {
        $facilityService->edit(
            $id,
            [
                'name' => $request->get('name'),
                'description' => $request->get('description') ?? '',
                'shorthand' => $request->get('shorthand'),
                'phone' => $request->get('phone') ?? '',
                'fax' => $request->get('fax') ?? '',
                'address1' => $request->get('address1'),
                'license' => $request->get('license') ?? '',
                'csz_id' => $request->get('csz_id'),
                'max_beds_number' => $request->get('max_beds_number')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/facility/{id} Delete Facility
     * @apiVersion 1.0.0
     * @apiName Delete Facility
     * @apiGroup Admin Facility
     * @apiDescription This function is used to remove facility
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
     *          "error": "Facility not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_delete", methods={"DELETE"})
     *
     * @param $id
     * @param FacilityService $facilityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, FacilityService $facilityService)
    {
        $facilityService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/facility Bulk Delete Facilities
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Facilities
     * @apiGroup Admin Facility
     * @apiDescription This function is used to bulk remove facilities
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
     *          "error": "Facility not found"
     *     }
     *
     * @Route("", name="api_admin_facility_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param FacilityService $facilityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, FacilityService $facilityService)
    {
        $facilityService->removeBulk(
            [
                'ids' => $request->get('ids')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
