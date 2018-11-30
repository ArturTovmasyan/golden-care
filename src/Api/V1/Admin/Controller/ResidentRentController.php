<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentRentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentRent;
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
 * @Route("/api/v1.0/admin/resident/rent")
 *
 * Class ResidentRentController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentRentController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/rent/grid Get ResidentRents Grid
     * @apiVersion 1.0.0
     * @apiName Get ResidentRents Grid
     * @apiGroup Admin Resident Rents
     * @apiDescription This function is used to listing residentRents
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the residentRent
     * @apiSuccess {Object}   resident        The resident of the residentRent
     * @apiSuccess {String}   start           The start date of the residentRent
     * @apiSuccess {String}   end             The end date of the residentRent
     * @apiSuccess {Int}      type            The type of the residentRent
     * @apiSuccess {Int}      amount          The amount of the residentRent
     * @apiSuccess {String}   notes           The notes of the residentRent
     * @apiSuccess {String}   source          The source of the residentRent
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
     *                  "start": "2016-10-01T00:00:00+00:00",
     *                  "end": null,
     *                  "type": 1,
     *                  "amount": 5000,
     *                  "notes": "some notes"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_rent_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentRentService $residentRentService)
    {
        return $this->respondGrid(
            $request,
            ResidentRent::class,
            'api_admin_resident_rent_grid',
            $residentRentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/rent/grid Get ResidentRent Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ResidentRent Grid Options
     * @apiGroup Admin Resident Rents
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the residentRent listing
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
     * @Route("/grid", name="api_admin_resident_rent_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentRent::class, 'api_admin_resident_rent_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/rent Get ResidentRents
     * @apiVersion 1.0.0
     * @apiName Get ResidentRents
     * @apiGroup Admin Resident Rents
     * @apiDescription This function is used to listing residentRents
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the residentRent
     * @apiSuccess {Object}   resident        The resident of the residentRent
     * @apiSuccess {String}   start           The start date of the residentRent
     * @apiSuccess {String}   end             The end date of the residentRent
     * @apiSuccess {Int}      type            The type of the residentRent
     * @apiSuccess {Int}      amount          The amount of the residentRent
     * @apiSuccess {String}   notes           The notes of the residentRent
     * @apiSuccess {String}   source          The source of the residentRent
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
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "start": "2016-10-01T00:00:00+00:00",
     *                  "end": null,
     *                  "type": 1,
     *                  "amount": 5000,
     *                  "notes": "some notes",
     *                  "source": "[{\"id \":\"1\",\"amount\":\"2500\"},{\"id \":\"2\",\"amount\":\"2500\"}]"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_resident_rent_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentRentService $residentRentService)
    {
        return $this->respondList(
            $request,
            ResidentRent::class,
            'api_admin_resident_rent_list',
            $residentRentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/rent/{id} Get ResidentRent
     * @apiVersion 1.0.0
     * @apiName Get ResidentRent
     * @apiGroup Admin Resident Rents
     * @apiDescription This function is used to get residentRent
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the residentRent
     * @apiSuccess {Object}   resident        The resident of the residentRent
     * @apiSuccess {String}   start           The start date of the residentRent
     * @apiSuccess {String}   end             The end date of the residentRent
     * @apiSuccess {Int}      type            The type of the residentRent
     * @apiSuccess {Int}      amount          The amount of the residentRent
     * @apiSuccess {String}   notes           The notes of the residentRent
     * @apiSuccess {String}   source          The source of the residentRent
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "start": "2016-10-01T00:00:00+00:00",
     *                  "end": null,
     *                  "type": 1,
     *                  "amount": 5000,
     *                  "notes": "some notes",
     *                  "source": "[{\"id \":\"1\",\"amount\":\"2500\"},{\"id \":\"2\",\"amount\":\"2500\"}]"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_get", methods={"GET"})
     *
     * @param ResidentRentService $residentRentService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentRentService $residentRentService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentRentService->getById($id),
            ['api_admin_resident_rent_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/rent Add ResidentRent
     * @apiVersion 1.0.0
     * @apiName Add ResidentRent
     * @apiGroup Admin Resident Rents
     * @apiDescription This function is used to add residentRent
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id     The unique identifier of the resident
     * @apiParam {String}  start           The start date of the residentRent
     * @apiParam {String}  end             The end date of the residentRent
     * @apiParam {Int}     type            The type of the residentRent
     * @apiParam {Float}   amount          The amount of the residentRent
     * @apiParam {String}  [notes]         The notes of the residentRent
     * @apiParam {Object}  [source]        The payment sources of the residentRent
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "start": "2016-10-01",
     *          "end": "",
     *          "type": 1,
     *          "amount": 5000,
     *          "notes": "some notes",
     *          "source": [
     *                        {
     *                          "id": 1,
     *                          "amount": 2500
     *                        },
     *                        {
     *                          "id": 2,
     *                          "amount": 2500
     *                        }
     *                    ]
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
     *              "amount": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_resident_rent_add", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResidentRentService $residentRentService)
    {
        $residentRentService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'start' => $request->get('start'),
                'end' => $request->get('end'),
                'type' => $request->get('type'),
                'amount' => $request->get('amount'),
                'notes' => $request->get('notes') ?? '',
                'source' => $request->get('source'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/rent/{id} Edit ResidentRent
     * @apiVersion 1.0.0
     * @apiName Edit ResidentRent
     * @apiGroup Admin Resident Rents
     * @apiDescription This function is used to edit residentRent
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id     The unique identifier of the resident
     * @apiParam {String}  start           The start date of the residentRent
     * @apiParam {String}  end             The end date of the residentRent
     * @apiParam {Int}     type            The type of the residentRent
     * @apiParam {Float}   amount          The amount of the residentRent
     * @apiParam {String}  [notes]         The notes of the residentRent
     * @apiParam {Array}   [source]        The payment sources of the residentRent
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "start": "2016-10-01",
     *          "end": "",
     *          "type": 1,
     *          "amount": 5000,
     *          "notes": "some notes",
     *          "source": [
     *                        {
     *                          "id": 1,
     *                          "amount": 2500
     *                        },
     *                        {
     *                          "id": 2,
     *                          "amount": 2500
     *                        }
     *                    ]
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
     *              "amount": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ResidentRentService $residentRentService)
    {
        $residentRentService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'start' => $request->get('start'),
                'end' => $request->get('end'),
                'type' => $request->get('type'),
                'amount' => $request->get('amount'),
                'notes' => $request->get('notes') ?? '',
                'source' => $request->get('source'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/rent/{id} Delete ResidentRent
     * @apiVersion 1.0.0
     * @apiName Delete ResidentRent
     * @apiGroup Admin Resident Rents
     * @apiDescription This function is used to remove residentRent
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
     *          "code": 639,
     *          "error": "ResidentRent not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_delete", methods={"DELETE"})
     *
     * @param $id
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentRentService $residentRentService)
    {
        $residentRentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/rent Bulk Delete ResidentRents
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ResidentRents
     * @apiGroup Admin Resident Rents
     * @apiDescription This function is used to bulk remove residentRents
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the residentRents
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
     *          "code": 639,
     *          "error": "ResidentRent not found"
     *     }
     *
     * @Route("", name="api_admin_resident_rent_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentRentService $residentRentService)
    {
        $residentRentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
