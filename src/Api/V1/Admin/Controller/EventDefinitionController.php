<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\EventDefinitionService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\EventDefinition;
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
 * @Route("/api/v1.0/admin/event/definition")
 *
 * @Grant(grant="persistence-common-event_definition", level="VIEW")
 *
 * Class EventDefinitionController
 * @package App\Api\V1\Admin\Controller
 */
class EventDefinitionController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/event/definition/grid Get EventDefinitions Grid
     * @apiVersion 1.0.0
     * @apiName Get EventDefinitions Grid
     * @apiGroup Admin Event Definitions
     * @apiDescription This function is used to listing eventDefinitions
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id                  The unique identifier of the eventDefinition
     * @apiSuccess {String}  title               The title of the eventDefinition
     * @apiSuccess {Boolean} ffc                 The show in FFC status of the eventDefinition
     * @apiSuccess {Boolean} ihc                 The show in IHC status of the eventDefinition
     * @apiSuccess {Boolean} il                  The show in IL status of the eventDefinition
     * @apiSuccess {Boolean} physician           The show physician field in event
     * @apiSuccess {Boolean} responsible_person  The show responsiblePerson field in event
     * @apiSuccess {Boolean} additional_date     The show additionalDate field in event
     * @apiSuccess {Object}  space               The space of the eventDefinition
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
     *                  "title": "911",
     *                  "ffc": true,
     *                  "ihc": true,
     *                  "il": true,
     *                  "physician": false,
     *                  "responsible_person": false,
     *                  "additional_date": false,
     *                  "space": "alms"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_event_definition_grid", methods={"GET"})
     *
     * @param Request $request
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, EventDefinitionService $eventDefinitionService)
    {
        return $this->respondGrid(
            $request,
            EventDefinition::class,
            'api_admin_event_definition_grid',
            $eventDefinitionService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/event/definition/grid Get EventDefinition Grid Options
     * @apiVersion 1.0.0
     * @apiName Get EventDefinition Grid Options
     * @apiGroup Admin Event Definitions
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the eventDefinition listing
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
     * @Route("/grid", name="api_admin_event_definition_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(EventDefinition::class, 'api_admin_event_definition_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/event/definition Get EventDefinitions
     * @apiVersion 1.0.0
     * @apiName Get EventDefinitions
     * @apiGroup Admin Event Definitions
     * @apiDescription This function is used to listing eventDefinitions
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id                  The unique identifier of the eventDefinition
     * @apiSuccess {String}  title               The title of the eventDefinition
     * @apiSuccess {Boolean} ffc                 The show in FFC status of the eventDefinition
     * @apiSuccess {Boolean} ihc                 The show in IHC status of the eventDefinition
     * @apiSuccess {Boolean} il                  The show in IL status of the eventDefinition
     * @apiSuccess {Boolean} physician           The show physician field in event
     * @apiSuccess {Boolean} responsible_person  The show responsiblePerson field in event
     * @apiSuccess {Boolean} additional_date     The show additionalDate field in event
     * @apiSuccess {Object}  space               The space of the eventDefinition
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
     *                  "title": "911",
     *                  "ffc": true,
     *                  "ihc": true,
     *                  "il": true,
     *                  "physician": false,
     *                  "responsible_person": false,
     *                  "additional_date": false,
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_event_definition_list", methods={"GET"})
     *
     * @param Request $request
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, EventDefinitionService $eventDefinitionService)
    {
        return $this->respondList(
            $request,
            EventDefinition::class,
            'api_admin_event_definition_list',
            $eventDefinitionService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/event/definition/{id} Get EventDefinition
     * @apiVersion 1.0.0
     * @apiName Get EventDefinition
     * @apiGroup Admin Event Definitions
     * @apiDescription This function is used to get eventDefinition
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id                  The unique identifier of the eventDefinition
     * @apiSuccess {String}  title               The title of the eventDefinition
     * @apiSuccess {Boolean} ffc                 The show in FFC status of the eventDefinition
     * @apiSuccess {Boolean} ihc                 The show in IHC status of the eventDefinition
     * @apiSuccess {Boolean} il                  The show in IL status of the eventDefinition
     * @apiSuccess {Boolean} physician           The show physician field in event
     * @apiSuccess {Boolean} responsible_person  The show responsiblePerson field in event
     * @apiSuccess {Boolean} additional_date     The show additionalDate field in event
     * @apiSuccess {Object}  space               The space of the eventDefinition
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "title": "911",
     *                  "ffc": true,
     *                  "ihc": true,
     *                  "il": true,
     *                  "physician": false,
     *                  "responsible_person": false,
     *                  "additional_date": false,
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_event_definition_get", methods={"GET"})
     *
     * @param EventDefinitionService $eventDefinitionService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, EventDefinitionService $eventDefinitionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $eventDefinitionService->getById($id),
            ['api_admin_event_definition_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/event/definition Add EventDefinition
     * @apiVersion 1.0.0
     * @apiName Add EventDefinition
     * @apiGroup Admin Event Definitions
     * @apiDescription This function is used to add eventDefinition
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam   {String}  title               The title of the eventDefinition
     * @apiParam   {Int}     ffc                 The show in FFC status of the eventDefinition
     * @apiParam   {Int}     ihc                 The show in IHC status of the eventDefinition
     * @apiParam   {Int}     il                  The show in IL status of the eventDefinition
     * @apiParam   {Int}     physician           The show physician field in event
     * @apiParam   {Int}     responsible_person  The show responsiblePerson field in event
     * @apiParam   {Int}     additional_date     The show additionalDate field in event
     * @apiParam   {Int}     space_id            The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "title": "911",
     *          "ffc": true,
     *          "ihc": true,
     *          "il": true,
     *          "physician": false,
     *          "responsible_person": false,
     *          "additional_date": false,
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
     *              "title": "Sorry, this title is already in use."
     *          }
     *     }
     *
     * @Route("", name="api_admin_event_definition_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-event_definition", level="ADD")
     *
     * @param Request $request
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, EventDefinitionService $eventDefinitionService)
    {
        $id = $eventDefinitionService->add(
            [
                'title' => $request->get('title'),
                'ffc' => $request->get('ffc'),
                'ihc' => $request->get('ihc'),
                'il' => $request->get('il'),
                'physician' => $request->get('physician'),
                'responsible_person' => $request->get('responsible_person'),
                'additional_date' => $request->get('additional_date'),
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
     * @api {put} /api/v1.0/admin/event/definition/{id} Edit EventDefinition
     * @apiVersion 1.0.0
     * @apiName Edit EventDefinition
     * @apiGroup Admin Event Definitions
     * @apiDescription This function is used to edit eventDefinition
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam   {String}  title               The title of the eventDefinition
     * @apiParam   {Int}     ffc                 The show in FFC status of the eventDefinition
     * @apiParam   {Int}     ihc                 The show in IHC status of the eventDefinition
     * @apiParam   {Int}     il                  The show in IL status of the eventDefinition
     * @apiParam   {Int}     physician           The show physician field in event
     * @apiParam   {Int}     responsible_person  The show responsiblePerson field in event
     * @apiParam   {Int}     additional_date     The show additionalDate field in event
     * @apiParam   {Int}     space_id            The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "title": "911",
     *          "ffc": true,
     *          "ihc": true,
     *          "il": true,
     *          "physician": false,
     *          "responsible_person": false,
     *          "additional_date": false,
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
     *              "title": "Sorry, this title is already in use."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_event_definition_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-event_definition", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, EventDefinitionService $eventDefinitionService)
    {
        $eventDefinitionService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'ffc' => $request->get('ffc'),
                'ihc' => $request->get('ihc'),
                'il' => $request->get('il'),
                'physician' => $request->get('physician'),
                'responsible_person' => $request->get('responsible_person'),
                'additional_date' => $request->get('additional_date'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/event/definition/{id} Delete EventDefinition
     * @apiVersion 1.0.0
     * @apiName Delete EventDefinition
     * @apiGroup Admin Event Definitions
     * @apiDescription This function is used to remove eventDefinition
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
     *          "error": "EventDefinition not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_event_definition_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-event_definition", level="DELETE")
     *
     * @param $id
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, EventDefinitionService $eventDefinitionService)
    {
        $eventDefinitionService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/event/definition Bulk Delete EventDefinitions
     * @apiVersion 1.0.0
     * @apiName Bulk Delete EventDefinitions
     * @apiGroup Admin Event Definitions
     * @apiDescription This function is used to bulk remove eventDefinitions
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the eventDefinitions
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
     *          "error": "EventDefinition not found"
     *     }
     *
     * @Route("", name="api_admin_event_definition_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-event_definition", level="DELETE")
     *
     * @param Request $request
     * @param EventDefinitionService $eventDefinitionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, EventDefinitionService $eventDefinitionService)
    {
        $eventDefinitionService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
