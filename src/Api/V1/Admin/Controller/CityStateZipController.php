<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\CityStateZipService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\CityStateZip;
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
 * @Route("/api/v1.0/admin/city/state/zip")
 *
 * Class CityStateZipController
 * @package App\Api\V1\Admin\Controller
 */
class CityStateZipController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/city/state/zip/grid Get CityStateZip Grid
     * @apiVersion 1.0.0
     * @apiName Get CityStateZip Grid
     * @apiGroup Admin CityStateZip
     * @apiDescription This function is used to listing cityStateZip
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id             The unique identifier of the cityStateZip
     * @apiSuccess {String}  state_full     The stateFull of the cityStateZip
     * @apiSuccess {String}  state_abbr     The stateAbbr of the cityStateZip
     * @apiSuccess {String}  zip_main       The zipMain of the cityStateZip
     * @apiSuccess {String}  zip_sub        The zipSub of the cityStateZip
     * @apiSuccess {String}  city           The city of the cityStateZip
     * @apiSuccess {String}  created_at     The created time of the cityStateZip
     * @apiSuccess {String}  updated_at     The updated time of the cityStateZip
     * @apiSuccess {Int}     created_by     The created user id of the cityStateZip
     * @apiSuccess {Int}     updated_by     The updated user id of the cityStateZip
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
     *                  "created_at": "2018-11-01 17:24:48",
     *                  "updated_at": "2018-11-01 17:25:49",
     *                  "created_by": 1,
     *                  "updated_by": 5,
     *                  "id": 1,
     *                  "state_full": "California",
     *                  "state_abbr": "CA",
     *                  "zip_main": "89439",
     *                  "zip_sub": "",
     *                  "city": "Verdi"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_city_state_zip_grid", methods={"GET"})
     *
     * @param Request $request
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, CityStateZipService $cityStateZipService)
    {
        return $this->respondGrid(
            $request,
            CityStateZip::class,
            'api_admin_city_state_zip_grid',
            $cityStateZipService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/city/state/zip/grid Get CityStateZip Grid Options
     * @apiVersion 1.0.0
     * @apiName Get CityStateZip Grid Options
     * @apiGroup Admin CityStateZip
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the cityStateZip listing
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
     * @Route("/grid", name="api_admin_city_state_zip_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(CityStateZip::class, 'api_admin_city_state_zip_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/city/state/zip Get CityStateZip
     * @apiVersion 1.0.0
     * @apiName Get CityStateZip
     * @apiGroup Admin CityStateZip
     * @apiDescription This function is used to listing cityStateZip
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id             The unique identifier of the cityStateZip
     * @apiSuccess {String}  state_full     The stateFull of the cityStateZip
     * @apiSuccess {String}  state_abbr     The stateAbbr of the cityStateZip
     * @apiSuccess {String}  zip_main       The zipMain of the cityStateZip
     * @apiSuccess {String}  zip_sub        The zipSub of the cityStateZip
     * @apiSuccess {String}  city           The city of the cityStateZip
     * @apiSuccess {String}  created_at     The created time of the cityStateZip
     * @apiSuccess {String}  updated_at     The updated time of the cityStateZip
     * @apiSuccess {Int}     created_by     The created user id of the cityStateZip
     * @apiSuccess {Int}     updated_by     The updated user id of the cityStateZip
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          [
     *              {
     *                  "created_at": "2018-11-01 17:24:48",
     *                  "updated_at": "2018-11-01 17:25:49",
     *                  "created_by": 1,
     *                  "updated_by": 5,
     *                  "id": 1,
     *                  "state_full": "California",
     *                  "state_abbr": "CA",
     *                  "zip_main": "89439",
     *                  "zip_sub": "",
     *                  "city": "Verdi"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_city_state_zip_list", methods={"GET"})
     *
     * @param Request $request
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, CityStateZipService $cityStateZipService)
    {
        return $this->respondGrid(
            $request,
            CityStateZip::class,
            'api_admin_city_state_zip_list',
            $cityStateZipService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/city/state/zip/{id} Get CityStateZip
     * @apiVersion 1.0.0
     * @apiName Get CityStateZip
     * @apiGroup Admin CityStateZip
     * @apiDescription This function is used to get cityStateZip
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id             The unique identifier of the cityStateZip
     * @apiSuccess {String}  state_full     The stateFull of the cityStateZip
     * @apiSuccess {String}  state_abbr     The stateAbbr of the cityStateZip
     * @apiSuccess {String}  zip_main       The zipMain of the cityStateZip
     * @apiSuccess {String}  zip_sub        The zipSub of the cityStateZip
     * @apiSuccess {String}  city           The city of the cityStateZip
     * @apiSuccess {String}  created_at     The created time of the cityStateZip
     * @apiSuccess {String}  updated_at     The updated time of the cityStateZip
     * @apiSuccess {Int}     created_by     The created user id of the cityStateZip
     * @apiSuccess {Int}     updated_by     The updated user id of the cityStateZip
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "created_at": "2018-11-01 17:24:48",
     *                  "updated_at": "2018-11-01 17:25:49",
     *                  "created_by": 1,
     *                  "updated_by": 5,
     *                  "id": 1,
     *                  "state_full": "California",
     *                  "state_abbr": "CA",
     *                  "zip_main": "89439",
     *                  "zip_sub": "",
     *                  "city": "Verdi"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_city_state_zip_get", methods={"GET"})
     *
     * @param CityStateZipService $cityStateZipService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, CityStateZipService $cityStateZipService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $cityStateZipService->getById($id),
            ['api_admin_city_state_zip_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/city/state/zip Add CityStateZip
     * @apiVersion 1.0.0
     * @apiName Add CityStateZip
     * @apiGroup Admin CityStateZip
     * @apiDescription This function is used to add cityStateZip
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  state_full       The stateFull of the cityStateZip
     * @apiParam {String}  state_abbr       The stateAbbr of the cityStateZip
     * @apiParam {String}  zip_main         The zipMain of the cityStateZip
     * @apiParam {String}  zip_sub          The zipSub of the cityStateZip
     * @apiParam {String}  city             The city of the cityStateZip
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "state_full": "California",
     *         "state_abbr": "CA",
     *         "zip_main": "89439",
     *         "zip_sub": "",
     *         "city": "Verdi"
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
     *              "title": "Sorry, this city should not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_city_state_zip_add", methods={"POST"})
     *
     * @param Request $request
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function addAction(Request $request, CityStateZipService $cityStateZipService)
    {
        $cityStateZipService->add(
            [
                'state_full' => $request->get('state_full'),
                'state_abbr' => $request->get('state_abbr'),
                'zip_main' => $request->get('zip_main'),
                'zip_sub' => $request->get('zip_sub'),
                'city' => $request->get('city')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/city/state/zip/{id} Edit CityStateZip
     * @apiVersion 1.0.0
     * @apiName Edit CityStateZip
     * @apiGroup Admin CityStateZip
     * @apiDescription This function is used to edit cityStateZip
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  state_full       The stateFull of the cityStateZip
     * @apiParam {String}  state_abbr       The stateAbbr of the cityStateZip
     * @apiParam {String}  zip_main         The zipMain of the cityStateZip
     * @apiParam {String}  zip_sub          The zipSub of the cityStateZip
     * @apiParam {String}  city             The city of the cityStateZip
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "state_full": "California",
     *         "state_abbr": "CA",
     *         "zip_main": "89439",
     *         "zip_sub": "",
     *         "city": "Verdi"
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
     *              "name": "Sorry, this city should not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_city_state_zip_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function editAction(Request $request, $id, CityStateZipService $cityStateZipService)
    {
        $cityStateZipService->edit(
            $id,
            [
                'state_full' => $request->get('state_full'),
                'state_abbr' => $request->get('state_abbr'),
                'zip_main' => $request->get('zip_main'),
                'zip_sub' => $request->get('zip_sub'),
                'city' => $request->get('city')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/city/state/zip/{id} Delete CityStateZip
     * @apiVersion 1.0.0
     * @apiName Delete CityStateZip
     * @apiGroup Admin CityStateZip
     * @apiDescription This function is used to remove cityStateZip
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
     *          "error": "CityStateZip not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_city_state_zip_delete", methods={"DELETE"})
     *
     * @param $id
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, CityStateZipService $cityStateZipService)
    {
        $cityStateZipService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/city/state/zip Bulk Delete CityStateZip
     * @apiVersion 1.0.0
     * @apiName Bulk Delete CityStateZip
     * @apiGroup Admin CityStateZip
     * @apiDescription This function is used to bulk remove cityStateZip
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the cityStateZips
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
     *          "error": "CityStateZip not found"
     *     }
     *
     * @Route("", name="api_admin_city_state_zip_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param CityStateZipService $cityStateZipService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, CityStateZipService $cityStateZipService)
    {
        $cityStateZipService->removeBulk(
            [
                'ids' => $request->get('ids')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
