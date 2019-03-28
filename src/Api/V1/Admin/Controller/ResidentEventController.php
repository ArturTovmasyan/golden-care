<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentEventService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentEvent;
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
 * @Route("/api/v1.0/admin/resident/event")
 *
 * @Grant(grant="persistence-resident-resident_event", level="VIEW")
 *
 * Class ResidentEventController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentEventController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/event/grid Get ResidentEvents Grid
     * @apiVersion 1.0.0
     * @apiName Get ResidentEvents Grid
     * @apiGroup Admin Resident Events
     * @apiDescription This function is used to listing residentEvents
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the residentEvent
     * @apiSuccess {Object}   resident        The resident of the residentEvent
     * @apiSuccess {String}   start           The start date of the residentEvent
     * @apiSuccess {String}   end             The end date of the residentEvent
     * @apiSuccess {Int}      type            The type of the residentEvent
     * @apiSuccess {Int}      amount          The amount of the residentEvent
     * @apiSuccess {String}   notes           The notes of the residentEvent
     * @apiSuccess {String}   source          The source of the residentEvent
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
     *                  "definition": "Dr. Visit",
     *                  "date": "2018-11-29T13:49:32+00:00",
     *                  "notes": "some notes",
     *                  "physician": "Mr. Firt Last",
     *                  "responsible_person": null,
     *                  "additional_date": null
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_event_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentEventService $residentEventService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentEventService $residentEventService)
    {
        return $this->respondGrid(
            $request,
            ResidentEvent::class,
            'api_admin_resident_event_grid',
            $residentEventService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/event/grid Get ResidentEvent Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ResidentEvent Grid Options
     * @apiGroup Admin Resident Events
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the residentEvent listing
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
     * @Route("/grid", name="api_admin_resident_event_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentEvent::class, 'api_admin_resident_event_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/event Get ResidentEvents
     * @apiVersion 1.0.0
     * @apiName Get ResidentEvents
     * @apiGroup Admin Resident Events
     * @apiDescription This function is used to listing residentEvents
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id              The unique identifier of the residentEvent
     * @apiSuccess {Object}   resident        The resident of the residentEvent
     * @apiSuccess {String}   start           The start date of the residentEvent
     * @apiSuccess {String}   end             The end date of the residentEvent
     * @apiSuccess {Int}      type            The type of the residentEvent
     * @apiSuccess {Int}      amount          The amount of the residentEvent
     * @apiSuccess {String}   notes           The notes of the residentEvent
     * @apiSuccess {String}   source          The source of the residentEvent
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
     *                  "definition": {
     *                      "id": 1,
     *                      "title": "Dr. Visit"
     *                  },
     *                  "date": "2018-11-29T13:49:32+00:00",
     *                  "notes": "some notes",
     *                  "physician": {
     *                      "id": 1,
     *                      "salutation": {
     *                          "id": 1,
     *                          "title": "Mr."
     *                      }
     *                      "first_name": "Firt",
     *                      "last_name": "Last"
     *                  },
     *                  "responsible_person": null,
     *                  "additional_date": null
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_resident_event_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentEventService $residentEventService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentEventService $residentEventService)
    {
        return $this->respondList(
            $request,
            ResidentEvent::class,
            'api_admin_resident_event_list',
            $residentEventService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/event/{id} Get ResidentEvent
     * @apiVersion 1.0.0
     * @apiName Get ResidentEvent
     * @apiGroup Admin Resident Events
     * @apiDescription This function is used to get residentEvent
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                  The unique identifier of the residentEvent
     * @apiSuccess {Object}   resident            The resident of the residentEvent
     * @apiSuccess {Object}   definition          The definition of the residentEvent
     * @apiSuccess {Object}   physician           The physician of the residentEvent
     * @apiSuccess {Object}   responsible_person  The resident of the residentEvent
     * @apiSuccess {String}   date                The  date of the residentEvent
     * @apiSuccess {String}   additional_date     The additional date of the residentEvent
     * @apiSuccess {String}   notes               The notes of the residentEvent
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "definition": {
     *                      "id": 1,
     *                      "title": "Dr. Visit"
     *                  },
     *                  "date": "2018-11-29T13:49:32+00:00",
     *                  "notes": "some notes",
     *                  "physician": {
     *                      "id": 1,
     *                      "salutation": {
     *                          "id": 1,
     *                          "title": "Mr."
     *                      }
     *                      "first_name": "Firt",
     *                      "last_name": "Last"
     *                  },
     *                  "responsible_person": null,
     *                  "additional_date": null
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_event_get", methods={"GET"})
     *
     * @param ResidentEventService $residentEventService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentEventService $residentEventService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentEventService->getById($id),
            ['api_admin_resident_event_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/event Add ResidentEvent
     * @apiVersion 1.0.0
     * @apiName Add ResidentEvent
     * @apiGroup Admin Resident Events
     * @apiDescription This function is used to add residentEvent
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id            The unique identifier of the resident
     * @apiParam {Int}     definition_id          The unique identifier of the definition
     * @apiParam {Int}     physician_id           The unique identifier of the physician
     * @apiParam {Int}     responsible_person_id  The unique identifier of the responsiblePerson
     * @apiParam {String}  date                   The date of the residentEvent
     * @apiParam {String}  additional_date        The additional date of the residentEvent
     * @apiParam {String}  [notes]                The notes of the residentEvent
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "definition_id": 1,
     *          "physician_id": 1,
     *          "responsible_person_id": "",
     *          "date": "2016-10-01",
     *          "additional_date": "",
     *          "notes": "some notes"
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
     *              "date": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_resident_event_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_event", level="ADD")
     *
     * @param Request $request
     * @param ResidentEventService $residentEventService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResidentEventService $residentEventService)
    {
        $id = $residentEventService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'definition_id' => $request->get('definition_id'),
                'physician_id' => $request->get('physician_id'),
                'responsible_persons' => $request->get('responsible_persons'),
                'additional_date' => $request->get('additional_date'),
                'date' => $request->get('date'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/event/{id} Edit ResidentEvent
     * @apiVersion 1.0.0
     * @apiName Edit ResidentEvent
     * @apiGroup Admin Resident Events
     * @apiDescription This function is used to edit residentEvent
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id            The unique identifier of the resident
     * @apiParam {Int}     physician_id           The unique identifier of the physician
     * @apiParam {Int}     responsible_person_id  The unique identifier of the responsiblePerson
     * @apiParam {String}  date                   The date of the residentEvent
     * @apiParam {String}  additional_date        The additional date of the residentEvent
     * @apiParam {String}  [notes]                The notes of the residentEvent
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "physician_id": 1,
     *          "responsible_person_id": "",
     *          "date": "2016-10-01",
     *          "additional_date": "",
     *          "notes": "some notes"
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
     *              "date": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_event_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_event", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentEventService $residentEventService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ResidentEventService $residentEventService)
    {
        $residentEventService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'physician_id' => $request->get('physician_id'),
                'responsible_persons' => $request->get('responsible_persons'),
                'additional_date' => $request->get('additional_date'),
                'date' => $request->get('date'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/event/{id} Delete ResidentEvent
     * @apiVersion 1.0.0
     * @apiName Delete ResidentEvent
     * @apiGroup Admin Resident Events
     * @apiDescription This function is used to remove residentEvent
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
     *          "error": "ResidentEvent not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_event_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_event", level="DELETE")
     *
     * @param $id
     * @param ResidentEventService $residentEventService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentEventService $residentEventService)
    {
        $residentEventService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/event Bulk Delete ResidentEvents
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ResidentEvents
     * @apiGroup Admin Resident Events
     * @apiDescription This function is used to bulk remove residentEvents
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the residentEvents
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
     *          "error": "ResidentEvent not found"
     *     }
     *
     * @Route("", name="api_admin_resident_event_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_event", level="DELETE")
     *
     * @param Request $request
     * @param ResidentEventService $residentEventService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentEventService $residentEventService)
    {
        $residentEventService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/event/related/info ResidentEvent related info
     * @apiVersion 1.0.0
     * @apiName ResidentEvent Related Info
     * @apiGroup Admin Resident Events
     * @apiDescription This function is used to get residentEvent related info
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
     *          "error": "ResidentEvent not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_resident_event_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentEventService $residentEventService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentEventService $residentEventService)
    {
        $relatedData = $residentEventService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
