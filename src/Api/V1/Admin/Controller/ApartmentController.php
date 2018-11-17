<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ApartmentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Apartment;
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
 * @Route("/api/v1.0/admin/apartment")
 *
 * Class ApartmentController
 * @package App\Api\V1\Admin\Controller
 */
class ApartmentController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/apartment/grid Get Apartments Grid
     * @apiVersion 1.0.0
     * @apiName Get Apartments Grid
     * @apiGroup Admin Apartment
     * @apiDescription This function is used to listing apartments
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the apartment
     * @apiSuccess {String}  name            The name of the apartment
     * @apiSuccess {String}  description     The description time of the apartment
     * @apiSuccess {String}  shorthand       The shorthand time of the apartment
     * @apiSuccess {String}  phone           The phone time of the apartment
     * @apiSuccess {String}  fax             The fax time of the apartment
     * @apiSuccess {String}  address1        The address1 time of the apartment
     * @apiSuccess {String}  license         The license time of the apartment
     * @apiSuccess {Object}  csz             The City State & Zip of the apartment
     * @apiSuccess {Int}     max_beds_number The maxBedsNumber time of the apartment
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
     *                  "name": "Auburn Oaks",
     *                  "description": "Some description",
     *                  "shorthand": "AOIL",
     *                  "phone": "(916) 729-9200",
     *                  "fax": "(916) 729-9204",
     *                  "address1": "Auburn Oaks",
     *                  "license": "347005555",
     *                  "csz_id": 1,
     *                  "csz_city": "Verdi",
     *                  "csz_state_abbr": "CA",
     *                  "csz_zip_main": "89439",
     *                  "max_beds_number": 48
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_apartment_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ApartmentService $apartmentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ApartmentService $apartmentService)
    {
        return $this->respondGrid(
            $request,
            Apartment::class,
            'api_admin_apartment_grid',
            $apartmentService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/apartment/grid Get Apartment Grid Options
     * @apiVersion 1.0.0
     * @apiName Get Apartment Grid Options
     * @apiGroup Admin Apartment
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the apartment listing
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
     * @Route("/grid", name="api_admin_apartment_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Apartment::class, 'api_admin_apartment_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/apartment Get Apartments
     * @apiVersion 1.0.0
     * @apiName Get Apartments
     * @apiGroup Admin Apartment
     * @apiDescription This function is used to listing apartments
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the apartment
     * @apiSuccess {String}  name            The name of the apartment
     * @apiSuccess {String}  description     The description time of the apartment
     * @apiSuccess {String}  shorthand       The shorthand time of the apartment
     * @apiSuccess {String}  phone           The phone time of the apartment
     * @apiSuccess {String}  fax             The fax time of the apartment
     * @apiSuccess {String}  address1        The address1 time of the apartment
     * @apiSuccess {String}  license         The license time of the apartment
     * @apiSuccess {Object}  csz             The City State & Zip of the apartment
     * @apiSuccess {Int}     max_beds_number The maxBedsNumber time of the apartment
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
     *                  "name": "Auburn Oaks",
     *                  "description": "Some description",
     *                  "shorthand": "AOIL",
     *                  "phone": "(916) 729-9200",
     *                  "fax": "(916) 729-9204",
     *                  "address1": "Auburn Oaks",
     *                  "license": "347005555",
     *                  "csz": {
     *                      "id": 1,
     *                      "state_abbr": "CA",
     *                      "zip_main": "89439",
     *                      "city": "Verdi"
     *                  },
     *                  "max_beds_number": 48
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_apartment_list", methods={"GET"})
     *
     * @param Request $request
     * @param ApartmentService $apartmentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ApartmentService $apartmentService)
    {
        return $this->respondList(
            $request,
            Apartment::class,
            'api_admin_apartment_list',
            $apartmentService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/apartment/{id} Get Apartment
     * @apiVersion 1.0.0
     * @apiName Get Apartment
     * @apiGroup Admin Apartment
     * @apiDescription This function is used to get apartment
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the apartment
     * @apiSuccess {String}  name            The name of the apartment
     * @apiSuccess {String}  description     The description time of the apartment
     * @apiSuccess {String}  shorthand       The shorthand time of the apartment
     * @apiSuccess {String}  phone           The phone time of the apartment
     * @apiSuccess {String}  fax             The fax time of the apartment
     * @apiSuccess {String}  address1        The address1 time of the apartment
     * @apiSuccess {String}  license         The license time of the apartment
     * @apiSuccess {Object}  csz             The City State & Zip of the apartment
     * @apiSuccess {Int}     max_beds_number The maxBedsNumber time of the apartment
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "name": "Auburn Oaks",
     *                  "description": "Some description",
     *                  "shorthand": "AOIL",
     *                  "phone": "(916) 729-9200",
     *                  "fax": "(916) 729-9204",
     *                  "address1": "Auburn Oaks",
     *                  "license": "347005555",
     *                  "csz": {
     *                      "id": 1,
     *                      "state_abbr": "CA",
     *                      "zip_main": "89439",
     *                      "city": "Verdi"
     *                  },
     *                  "max_beds_number": 48
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_get", methods={"GET"})
     *
     * @param ApartmentService $apartmentService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ApartmentService $apartmentService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $apartmentService->getById($id),
            ['api_admin_apartment_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/apartment Add Apartment
     * @apiVersion 1.0.0
     * @apiName Add Apartment
     * @apiGroup Admin Apartment
     * @apiDescription This function is used to add apartment
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name            The name of the apartment
     * @apiParam {String}  [description]   The description of the apartment
     * @apiParam {String}  shorthand       The shorthand of the apartment
     * @apiParam {String}  [phone]         The phone of the apartment
     * @apiParam {String}  [fax]           The fax of the apartment
     * @apiParam {String}  address1        The address1 of the apartment
     * @apiParam {String}  [license]       The license of the apartment
     * @apiParam {Int}     csz_id          The unique identifier of the City State & Zip
     * @apiParam {Int}     max_beds_number The maxBedsNumber of the apartment
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "name": "Auburn Oaks",
     *          "description": "Some description",
     *          "shorthand": "AOIL",
     *          "phone": "(916) 729-9200",
     *          "fax": "(916) 729-9204",
     *          "address1": "Auburn Oaks",
     *          "license": "347005555",
     *          "csz_id": 1,
     *          "max_beds_number": 48
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
     * @Route("", name="api_admin_apartment_add", methods={"POST"})
     *
     * @param Request $request
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, ApartmentService $apartmentService)
    {
        $apartmentService->add(
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
     * @api {put} /api/v1.0/admin/apartment/{id} Edit Apartment
     * @apiVersion 1.0.0
     * @apiName Edit Apartment
     * @apiGroup Admin Apartment
     * @apiDescription This function is used to edit apartment
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  name            The name of the apartment
     * @apiParam {String}  [description]   The description of the apartment
     * @apiParam {String}  shorthand       The shorthand of the apartment
     * @apiParam {String}  [phone]         The phone of the apartment
     * @apiParam {String}  [fax]           The fax of the apartment
     * @apiParam {String}  address1        The address1 of the apartment
     * @apiParam {String}  [license]       The license of the apartment
     * @apiParam {Int}     csz_id          The unique identifier of the City State & Zip
     * @apiParam {Int}     max_beds_number The maxBedsNumber of the apartment
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "name": "Auburn Oaks",
     *          "description": "Some description",
     *          "shorthand": "AOIL",
     *          "phone": "(916) 729-9200",
     *          "fax": "(916) 729-9204",
     *          "address1": "Auburn Oaks",
     *          "license": "347005555",
     *          "csz_id": 1,
     *          "max_beds_number": 48
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, ApartmentService $apartmentService)
    {
        $apartmentService->edit(
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
     * @api {delete} /api/v1.0/admin/apartment/{id} Delete Apartment
     * @apiVersion 1.0.0
     * @apiName Delete Apartment
     * @apiGroup Admin Apartment
     * @apiDescription This function is used to remove apartment
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
     *          "error": "Apartment not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_delete", methods={"DELETE"})
     *
     * @param $id
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ApartmentService $apartmentService)
    {
        $apartmentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/apartment Bulk Delete Apartments
     * @apiVersion 1.0.0
     * @apiName Bulk Delete Apartments
     * @apiGroup Admin Apartment
     * @apiDescription This function is used to bulk remove apartments
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the apartments
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
     *          "error": "Apartment not found"
     *     }
     *
     * @Route("", name="api_admin_apartment_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param ApartmentService $apartmentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ApartmentService $apartmentService)
    {
        $apartmentService->removeBulk(
            [
                'ids' => $request->get('ids')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
