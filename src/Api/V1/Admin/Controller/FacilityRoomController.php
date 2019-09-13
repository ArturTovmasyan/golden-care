<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\FacilityRoomService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\FacilityRoom;
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
 * @Route("/api/v1.0/admin/facility/room")
 *
 * @Grant(grant="persistence-facility_room", level="VIEW")
 *
 * Class FacilityRoomController
 * @package App\Api\V1\Admin\Controller
 */
class FacilityRoomController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/facility/room/grid Get FacilityRooms Grid
     * @apiVersion 1.0.0
     * @apiName Get FacilityRooms Grid
     * @apiGroup Admin FacilityRoom
     * @apiDescription This function is used to listing facilityRooms
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the facilityRoom
     * @apiSuccess {Object}   facility        The facility of the facilityRoom
     * @apiSuccess {String}   number          The number of the facilityRoom
     * @apiSuccess {Int}      floor           The floor of the facilityRoom
     * @apiSuccess {String}   notes           The notes of the facilityRoom
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
     *                  "number": "101",
     *                  "floor": 1,
     *                  "notes": "some notes",
     *                  "facility": "Citrus Heights Terrace"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_facility_room_grid", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, FacilityRoomService $facilityRoomService)
    {
        return $this->respondGrid(
            $request,
            FacilityRoom::class,
            'api_admin_facility_room_grid',
            $facilityRoomService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/facility/room/grid Get FacilityRoom Grid Options
     * @apiVersion 1.0.0
     * @apiName Get FacilityRoom Grid Options
     * @apiGroup Admin FacilityRoom
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the facilityRoom listing
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
     * @Route("/grid", name="api_admin_facility_room_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, FacilityRoom::class, 'api_admin_facility_room_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/facility/room Get FacilityRooms
     * @apiVersion 1.0.0
     * @apiName Get FacilityRooms
     * @apiGroup Admin FacilityRoom
     * @apiDescription This function is used to listing facilityRooms
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the facilityRoom
     * @apiSuccess {Object}   facility        The facility of the facilityRoom
     * @apiSuccess {String}   number          The number of the facilityRoom
     * @apiSuccess {Int}      floor           The floor of the facilityRoom
     * @apiSuccess {String}   notes           The notes of the facilityRoom
     * @apiSuccess {Array}    beds            The beds of the facilityRoom
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
     *                  "facility": {
     *                      "id": 1,
     *                      "name": "Citrus Heights Terrace"
     *                  },
     *                  "number": "101",
     *                  "floor": 1,
     *                  "notes": "some notes",
     *                  "beds": [
     *                        {
     *                          "id": 9,
     *                          "number": "A"
     *                        },
     *                        {
     *                          "id": 10,
     *                          "number": "B"
     *                        },
     *                        {
     *                          "id": 11,
     *                          "number": "C"
     *                        },
     *                        {
     *                          "id": 12,
     *                          "number": "D"
     *                        }
     *                    ]
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_facility_room_list", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, FacilityRoomService $facilityRoomService)
    {
        return $this->respondList(
            $request,
            FacilityRoom::class,
            'api_admin_facility_room_list',
            $facilityRoomService,
            [
                'facility_id' => $request->get('facility_id'),
                'vacant' => $request->get('vacant')
            ]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/facility/room/{id} Get FacilityRoom
     * @apiVersion 1.0.0
     * @apiName Get FacilityRoom
     * @apiGroup Admin FacilityRoom
     * @apiDescription This function is used to get facilityRoom
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the facilityRoom
     * @apiSuccess {Object}   facility        The facility of the facilityRoom
     * @apiSuccess {String}   number          The number of the facilityRoom
     * @apiSuccess {Int}      floor           The floor of the facilityRoom
     * @apiSuccess {String}   notes           The notes of the facilityRoom
     * @apiSuccess {Array}    beds            The beds of the facilityRoom
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "facility": {
     *                      "id": 1,
     *                      "name": "Citrus Heights Terrace"
     *                  },
     *                  "number": "101",
     *                  "floor": 1,
     *                  "notes": "some notes",
     *                  "beds": [
     *                        {
     *                          "id": 9,
     *                          "number": "A"
     *                        },
     *                        {
     *                          "id": 10,
     *                          "number": "B"
     *                        },
     *                        {
     *                          "id": 11,
     *                          "number": "C"
     *                        },
     *                        {
     *                          "id": 12,
     *                          "number": "D"
     *                        }
     *                    ]
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_room_get", methods={"GET"})
     *
     * @param FacilityRoomService $facilityRoomService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, FacilityRoomService $facilityRoomService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $facilityRoomService->getById($id),
            ['api_admin_facility_room_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/facility/room Add FacilityRoom
     * @apiVersion 1.0.0
     * @apiName Add FacilityRoom
     * @apiGroup Admin FacilityRoom
     * @apiDescription This function is used to add facilityRoom
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     facility_id     The unique identifier of the facility
     * @apiParam {String}  number          The number of the facilityRoom
     * @apiParam {Int}     floor           The floor of the facilityRoom
     * @apiParam {String}  [notes]         The notes of the facilityRoom
     * @apiParam {Array}   beds            The beds of the facilityRoom
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "facility_id": 1,
     *          "number": "101",
     *          "floor": 1,
     *          "notes": "some notes",
     *          "beds": [
     *                        {
     *                          "id": "",
     *                          "number": "A"
     *                        },
     *                        {
     *                          "id": "",
     *                          "number": "B"
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
     *              "number": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_facility_room_add", methods={"POST"})
     *
     * @Grant(grant="persistence-facility_room", level="ADD")
     *
     * @param Request $request
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, FacilityRoomService $facilityRoomService)
    {
        $id = $facilityRoomService->add(
            [
                'facility_id' => $request->get('facility_id'),
                'number' => $request->get('number'),
                'floor' => $request->get('floor'),
                'notes' => $request->get('notes') ?? '',
                'beds' => $request->get('beds')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/facility/room/{id} Edit FacilityRoom
     * @apiVersion 1.0.0
     * @apiName Edit FacilityRoom
     * @apiGroup Admin FacilityRoom
     * @apiDescription This function is used to edit facilityRoom
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     facility_id     The unique identifier of the facility
     * @apiParam {String}  number          The number of the facilityRoom
     * @apiParam {Int}     floor           The floor of the facilityRoom
     * @apiParam {String}  [notes]         The notes of the facilityRoom
     * @apiParam {Array}   beds            The beds of the facilityRoom
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "facility_id": 1,
     *          "number": "101",
     *          "floor": 1,
     *          "notes": "some notes",
     *          "beds": [
     *                        {
     *                          "id": 9,
     *                          "number": "A"
     *                        },
     *                        {
     *                          "id": 10,
     *                          "number": "B"
     *                        },
     *                        {
     *                          "id": "",
     *                          "number": "C"
     *                        },
     *                        {
     *                          "id": "",
     *                          "number": "D"
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
     *              "number": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_room_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-facility_room", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, FacilityRoomService $facilityRoomService)
    {
        $facilityRoomService->edit(
            $id,
            [
                'facility_id' => $request->get('facility_id'),
                'number' => $request->get('number'),
                'floor' => $request->get('floor'),
                'notes' => $request->get('notes') ?? '',
                'beds' => $request->get('beds')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/facility/room/{id} Delete FacilityRoom
     * @apiVersion 1.0.0
     * @apiName Delete FacilityRoom
     * @apiGroup Admin FacilityRoom
     * @apiDescription This function is used to remove facilityRoom
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
     *          "error": "FacilityRoom not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_room_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_room", level="DELETE")
     *
     * @param $id
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, FacilityRoomService $facilityRoomService)
    {
        $facilityRoomService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/facility/room Bulk Delete FacilityRooms
     * @apiVersion 1.0.0
     * @apiName Bulk Delete FacilityRooms
     * @apiGroup Admin FacilityRoom
     * @apiDescription This function is used to bulk remove facilityRooms
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the facilityRooms
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
     *          "error": "FacilityRoom not found"
     *     }
     *
     * @Route("", name="api_admin_facility_room_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_room", level="DELETE")
     *
     * @param Request $request
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, FacilityRoomService $facilityRoomService)
    {
        $facilityRoomService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     *
     * @Route("/{facility_id}/last", requirements={"facility_id"="\d+"}, name="api_admin_facility_room_get_last", methods={"GET"})
     *
     * @Grant(grant="persistence-facility_room", level="VIEW")
     *
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     */
    public function getLastAction(Request $request, $facility_id, FacilityRoomService $facilityRoomService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$facilityRoomService->getLastNumber($facility_id)],
            ['api_admin_apartment_room_get_last']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/facility/room/related/info FacilityRoom related info
     * @apiVersion 1.0.0
     * @apiName FacilityRoom Related Info
     * @apiGroup Admin FacilityRoom
     * @apiDescription This function is used to get facilityRoom related info
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the facilityRooms
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
     *          "error": "FacilityRoom not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_facility_room_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, FacilityRoomService $facilityRoomService)
    {
        $relatedData = $facilityRoomService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
