<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ApartmentRoomService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ApartmentRoom;
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
 * @Route("/api/v1.0/admin/apartment/room")
 *
 * Class ApartmentRoomController
 * @package App\Api\V1\Admin\Controller
 */
class ApartmentRoomController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/apartment/room/grid Get ApartmentRooms Grid
     * @apiVersion 1.0.0
     * @apiName Get ApartmentRooms Grid
     * @apiGroup Admin ApartmentRoom
     * @apiDescription This function is used to listing apartmentRooms
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the apartmentRoom
     * @apiSuccess {Object}   apartment       The apartment of the apartmentRoom
     * @apiSuccess {String}   number          The number of the apartmentRoom
     * @apiSuccess {Int}      type            The type of the apartmentRoom
     * @apiSuccess {Int}      floor           The floor of the apartmentRoom
     * @apiSuccess {Boolean}  disabled        The disabled status of the apartmentRoom
     * @apiSuccess {Boolean}  shared          The shared status of the apartmentRoom
     * @apiSuccess {String}   notes           The notes of the apartmentRoom
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
     *                  "apartment": "Auburn Oaks",
     *                  "number": "101",
     *                  "type": 1,
     *                  "floor": 1,
     *                  "disabled": false,
     *                  "shared": false,
     *                  "notes": "some notes",
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_apartment_room_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ApartmentRoomService $apartmentRoomService)
    {
        return $this->respondGrid(
            $request,
            ApartmentRoom::class,
            'api_admin_apartment_room_grid',
            $apartmentRoomService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/apartment/room/grid Get ApartmentRoom Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ApartmentRoom Grid Options
     * @apiGroup Admin ApartmentRoom
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the apartmentRoom listing
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
     * @Route("/grid", name="api_admin_apartment_room_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ApartmentRoom::class, 'api_admin_apartment_room_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/apartment/room Get ApartmentRooms
     * @apiVersion 1.0.0
     * @apiName Get ApartmentRooms
     * @apiGroup Admin ApartmentRoom
     * @apiDescription This function is used to listing apartmentRooms
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the apartmentRoom
     * @apiSuccess {Object}   apartment       The apartment of the apartmentRoom
     * @apiSuccess {String}   number          The number of the apartmentRoom
     * @apiSuccess {Int}      type            The type of the apartmentRoom
     * @apiSuccess {Int}      floor           The floor of the apartmentRoom
     * @apiSuccess {Boolean}  disabled        The disabled status of the apartmentRoom
     * @apiSuccess {Boolean}  shared          The shared status of the apartmentRoom
     * @apiSuccess {String}   notes           The notes of the apartmentRoom
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
     *                  "apartment": {
     *                      "id": 1,
     *                      "name": "Auburn Oaks"
     *                  },
     *                  "number": "101",
     *                  "type": 1,
     *                  "floor": 1,
     *                  "disabled": false,
     *                  "shared": false,
     *                  "notes": "some notes"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_apartment_room_list", methods={"GET"})
     *
     * @param Request $request
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ApartmentRoomService $apartmentRoomService)
    {
        return $this->respondList(
            $request,
            ApartmentRoom::class,
            'api_admin_apartment_room_list',
            $apartmentRoomService,
            ['apartment_id' => $request->get('apartment_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/apartment/room/{id} Get ApartmentRoom
     * @apiVersion 1.0.0
     * @apiName Get ApartmentRoom
     * @apiGroup Admin ApartmentRoom
     * @apiDescription This function is used to get apartmentRoom
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the apartmentRoom
     * @apiSuccess {Object}   apartment       The apartment of the apartmentRoom
     * @apiSuccess {String}   number          The number of the apartmentRoom
     * @apiSuccess {Int}      type            The type of the apartmentRoom
     * @apiSuccess {Int}      floor           The floor of the apartmentRoom
     * @apiSuccess {Boolean}  disabled        The disabled status of the apartmentRoom
     * @apiSuccess {Boolean}  shared          The shared status of the apartmentRoom
     * @apiSuccess {String}   notes           The notes of the apartmentRoom
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "apartment": {
     *                      "id": 1,
     *                      "name": "Auburn Oaks"
     *                  },
     *                  "number": "101",
     *                  "type": 1,
     *                  "floor": 1,
     *                  "disabled": false,
     *                  "shared": false,
     *                  "notes": "some notes"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_room_get", methods={"GET"})
     *
     * @param ApartmentRoomService $apartmentRoomService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ApartmentRoomService $apartmentRoomService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $apartmentRoomService->getById($id),
            ['api_admin_apartment_room_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/apartment/room Add ApartmentRoom
     * @apiVersion 1.0.0
     * @apiName Add ApartmentRoom
     * @apiGroup Admin ApartmentRoom
     * @apiDescription This function is used to add apartmentRoom
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     apartment_id    The unique identifier of the apartment
     * @apiParam {String}  number          The number of the apartmentRoom
     * @apiParam {Int}     type            The type of the apartmentRoom
     * @apiParam {Int}     floor           The floor of the apartmentRoom
     * @apiParam {Int}     disabled        The disabled status of the apartmentRoom
     * @apiParam {Int}     shared          The shared status of the apartmentRoom
     * @apiParam {String}  [notes]         The notes of the apartmentRoom
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "apartment_id": 1,
     *          "number": "101",
     *          "type": 1,
     *          "floor": 1,
     *          "disabled": 0,
     *          "shared": 0,
     *          "notes": "some notes",
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
     * @Route("", name="api_admin_apartment_room_add", methods={"POST"})
     *
     * @param Request $request
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ApartmentRoomService $apartmentRoomService)
    {
        $apartmentRoomService->add(
            [
                'apartment_id' => $request->get('apartment_id'),
                'number' => $request->get('number'),
                'type' => $request->get('type'),
                'floor' => $request->get('floor'),
                'disabled' => $request->get('disabled'),
                'shared' => $request->get('shared'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/apartment/room/{id} Edit ApartmentRoom
     * @apiVersion 1.0.0
     * @apiName Edit ApartmentRoom
     * @apiGroup Admin ApartmentRoom
     * @apiDescription This function is used to edit apartmentRoom
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     apartment_id    The unique identifier of the apartment
     * @apiParam {String}  number          The number of the apartmentRoom
     * @apiParam {Int}     type            The type of the apartmentRoom
     * @apiParam {Int}     floor           The floor of the apartmentRoom
     * @apiParam {Int}     disabled        The disabled status of the apartmentRoom
     * @apiParam {Int}     shared          The shared status of the apartmentRoom
     * @apiParam {String}  [notes]         The notes of the apartmentRoom
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "apartment_id": 1,
     *          "number": "101",
     *          "type": 1,
     *          "floor": 1,
     *          "disabled": 0,
     *          "shared": 0,
     *          "notes": "some notes",
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_room_edit", methods={"PUT"})
     *
     * @param Request $request
     * @param $id
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ApartmentRoomService $apartmentRoomService)
    {
        $apartmentRoomService->edit(
            $id,
            [
                'apartment_id' => $request->get('apartment_id'),
                'number' => $request->get('number'),
                'type' => $request->get('type'),
                'floor' => $request->get('floor'),
                'disabled' => $request->get('disabled'),
                'shared' => $request->get('shared'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/apartment/room/{id} Delete ApartmentRoom
     * @apiVersion 1.0.0
     * @apiName Delete ApartmentRoom
     * @apiGroup Admin ApartmentRoom
     * @apiDescription This function is used to remove apartmentRoom
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
     *          "error": "ApartmentRoom not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_apartment_room_delete", methods={"DELETE"})
     *
     * @param $id
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ApartmentRoomService $apartmentRoomService)
    {
        $apartmentRoomService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/apartment/room Bulk Delete ApartmentRooms
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ApartmentRooms
     * @apiGroup Admin ApartmentRoom
     * @apiDescription This function is used to bulk remove apartmentRooms
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the apartmentRooms
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
     *          "error": "ApartmentRoom not found"
     *     }
     *
     * @Route("", name="api_admin_apartment_room_delete_bulk", methods={"DELETE"})
     *
     * @param Request $request
     * @param ApartmentRoomService $apartmentRoomService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ApartmentRoomService $apartmentRoomService)
    {
        $apartmentRoomService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
