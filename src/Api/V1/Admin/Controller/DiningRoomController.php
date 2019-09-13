<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\DiningRoomService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\DiningRoom;
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
 * @Route("/api/v1.0/admin/facility/dining/room")
 *
 * @Grant(grant="persistence-dining_room", level="VIEW")
 *
 * Class DiningRoomController
 * @package App\Api\V1\Admin\Controller
 */
class DiningRoomController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/facility/dining/room/grid Get DiningRooms Grid
     * @apiVersion 1.0.0
     * @apiName Get DiningRooms Grid
     * @apiGroup Admin DiningRoom
     * @apiDescription This function is used to listing diningRooms
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the diningRoom
     * @apiSuccess {String}  title           The title of the diningRoom
     * @apiSuccess {Object}  facility        The facility of the diningRoom
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
     *                  "title": "North Dining Room",
     *                  "facility": "Citrus Heights Terrace"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_dining_room_grid", methods={"GET"})
     *
     * @param Request $request
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, DiningRoomService $diningRoomService)
    {
        return $this->respondGrid(
            $request,
            DiningRoom::class,
            'api_admin_dining_room_grid',
            $diningRoomService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/facility/dining/room/grid Get DiningRoom Grid Options
     * @apiVersion 1.0.0
     * @apiName Get DiningRoom Grid Options
     * @apiGroup Admin DiningRoom
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the diningRoom listing
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
     * @Route("/grid", name="api_admin_dining_room_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, DiningRoom::class, 'api_admin_dining_room_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/facility/dining/room Get DiningRooms
     * @apiVersion 1.0.0
     * @apiName Get DiningRooms
     * @apiGroup Admin DiningRoom
     * @apiDescription This function is used to listing diningRooms
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the diningRoom
     * @apiSuccess {String}  title           The title of the diningRoom
     * @apiSuccess {Object}  facility        The facility of the diningRoom
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
     *                  "title": "North Dining Room",
     *                  "facility": {
     *                      "id": 1,
     *                      "name": "Citrus Heights Terrace"
     *                  }
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_dining_room_list", methods={"GET"})
     *
     * @param Request $request
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, DiningRoomService $diningRoomService)
    {
        return $this->respondList(
            $request,
            DiningRoom::class,
            'api_admin_dining_room_list',
            $diningRoomService,
            ['facility_id' => $request->get('facility_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/facility/dining/room/{id} Get DiningRoom
     * @apiVersion 1.0.0
     * @apiName Get DiningRoom
     * @apiGroup Admin DiningRoom
     * @apiDescription This function is used to get diningRoom
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id              The unique identifier of the diningRoom
     * @apiSuccess {String}  title           The title of the diningRoom
     * @apiSuccess {Object}  facility        The facility of the diningRoom
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "title": "North Dining Room",
     *                  "facility": {
     *                      "id": 1,
     *                      "name": "Citrus Heights Terrace"
     *                  }
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_dining_room_get", methods={"GET"})
     *
     * @param DiningRoomService $diningRoomService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, DiningRoomService $diningRoomService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $diningRoomService->getById($id),
            ['api_admin_dining_room_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/facility/dining/room Add DiningRoom
     * @apiVersion 1.0.0
     * @apiName Add DiningRoom
     * @apiGroup Admin DiningRoom
     * @apiDescription This function is used to add diningRoom
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title           The title of the diningRoom
     * @apiParam {Int}     facility_id     The unique identifier of the facility
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "title": "North Dining Room",
     *          "facility_id": 1
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
     *              "title": "Sorry, this title is already in use."
     *          }
     *     }
     *
     * @Route("", name="api_admin_dining_room_add", methods={"POST"})
     *
     * @Grant(grant="persistence-dining_room", level="ADD")
     *
     * @param Request $request
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, DiningRoomService $diningRoomService)
    {
        $id = $diningRoomService->add(
            [
                'title'       => $request->get('title'),
                'facility_id' => $request->get('facility_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/facility/dining/room/{id} Edit DiningRoom
     * @apiVersion 1.0.0
     * @apiName Edit DiningRoom
     * @apiGroup Admin DiningRoom
     * @apiDescription This function is used to edit diningRoom
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title           The title of the diningRoom
     * @apiParam {Int}     facility_id     The unique identifier of the facility
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "title": "North Dining Room",
     *          "facility_id": 1
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
     *              "title": "Sorry, this title is already in use."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_dining_room_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-dining_room", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, DiningRoomService $diningRoomService)
    {
        $diningRoomService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'facility_id' => $request->get('facility_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/facility/dining/room/{id} Delete DiningRoom
     * @apiVersion 1.0.0
     * @apiName Delete DiningRoom
     * @apiGroup Admin DiningRoom
     * @apiDescription This function is used to remove diningRoom
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
     *          "error": "DiningRoom not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_dining_room_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-dining_room", level="DELETE")
     *
     * @param $id
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, DiningRoomService $diningRoomService)
    {
        $diningRoomService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/facility/dining/room Bulk Delete DiningRooms
     * @apiVersion 1.0.0
     * @apiName Bulk Delete DiningRooms
     * @apiGroup Admin DiningRoom
     * @apiDescription This function is used to bulk remove diningRooms
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the diningRooms
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
     *          "error": "DiningRoom not found"
     *     }
     *
     * @Route("", name="api_admin_dining_room_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-dining_room", level="DELETE")
     *
     * @param Request $request
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, DiningRoomService $diningRoomService)
    {
        $diningRoomService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/facility/dining/room/related/info DiningRoom related info
     * @apiVersion 1.0.0
     * @apiName DiningRoom Related Info
     * @apiGroup Admin DiningRoom
     * @apiDescription This function is used to get diningRoom related info
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
     *          "error": "DiningRoom not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_dining_room_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param DiningRoomService $diningRoomService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, DiningRoomService $diningRoomService)
    {
        $relatedData = $diningRoomService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
