<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentMedicalHistoryConditionService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentMedicalHistoryCondition;
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
 * @Route("/api/v1.0/admin/resident/history/medical/history")
 *
 * @Grant(grant="persistence-resident-resident_medical_history_condition", level="VIEW")
 *
 * Class ResidentMedicalHistoryConditionController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentMedicalHistoryConditionController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/history/medical/history/grid Get ResidentMedicalHistoryConditions Grid
     * @apiVersion 1.0.0
     * @apiName Get ResidentMedicalHistoryConditions Grid
     * @apiGroup Admin Resident Medical History Conditions
     * @apiDescription This function is used to listing residentMedicalHistoryConditions
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentMedicalHistoryCondition
     * @apiSuccess {Object}   resident             The resident of the residentMedicalHistoryCondition
     * @apiSuccess {Object}   condition            The condition of the residentMedicalHistoryCondition
     * @apiSuccess {String}   date                 The date of the residentMedicalHistoryCondition
     * @apiSuccess {String}   notes                The notes of the residentMedicalHistoryCondition
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
     *                   "id": 1,
     *                   "condition": "Neuropathy",
     *                   "date": "1929-11-12T00:00:00+00:00",
     *                   "notes": "some notes"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_medical_history_condition_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService)
    {
        return $this->respondGrid(
            $request,
            ResidentMedicalHistoryCondition::class,
            'api_admin_resident_medical_history_condition_grid',
            $residentMedicalHistoryConditionService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/history/medical/history/grid Get ResidentMedicalHistoryCondition Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ResidentMedicalHistoryCondition Grid Options
     * @apiGroup Admin Resident Medical History Conditions
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the residentMedicalHistoryCondition listing
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
     * @Route("/grid", name="api_admin_resident_medical_history_condition_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentMedicalHistoryCondition::class, 'api_admin_resident_medical_history_condition_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/history/medical/history Get ResidentMedicalHistoryConditions
     * @apiVersion 1.0.0
     * @apiName Get ResidentMedicalHistoryConditions
     * @apiGroup Admin Resident Medical History Conditions
     * @apiDescription This function is used to listing residentMedicalHistoryConditions
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentMedicalHistoryCondition
     * @apiSuccess {Object}   resident             The resident of the residentMedicalHistoryCondition
     * @apiSuccess {Object}   condition            The condition of the residentMedicalHistoryCondition
     * @apiSuccess {String}   date                 The date of the residentMedicalHistoryCondition
     * @apiSuccess {String}   notes                The notes of the residentMedicalHistoryCondition
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
     *                  "condition": {
     *                      "id": 1,
     *                      "title": "Neuropathy"
     *                  },
     *                  "date": "1929-11-12T00:00:00+00:00",
     *                  "notes": "some notes"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_resident_medical_history_condition_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService)
    {
        return $this->respondList(
            $request,
            ResidentMedicalHistoryCondition::class,
            'api_admin_resident_medical_history_condition_list',
            $residentMedicalHistoryConditionService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/history/medical/history/{id} Get ResidentMedicalHistoryCondition
     * @apiVersion 1.0.0
     * @apiName Get ResidentMedicalHistoryCondition
     * @apiGroup Admin Resident Medical History Conditions
     * @apiDescription This function is used to get residentMedicalHistoryCondition
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentMedicalHistoryCondition
     * @apiSuccess {Object}   resident             The resident of the residentMedicalHistoryCondition
     * @apiSuccess {Object}   condition            The condition of the residentMedicalHistoryCondition
     * @apiSuccess {String}   date                 The date of the residentMedicalHistoryCondition
     * @apiSuccess {String}   notes                The notes of the residentMedicalHistoryCondition
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "condition": {
     *                      "id": 1,
     *                      "title": "Neuropathy"
     *                  },
     *                  "date": "1929-11-12T00:00:00+00:00",
     *                  "notes": "some notes"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medical_history_condition_get", methods={"GET"})
     *
     * @param ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentMedicalHistoryConditionService->getById($id),
            ['api_admin_resident_medical_history_condition_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/history/medical/history Add ResidentMedicalHistoryCondition
     * @apiVersion 1.0.0
     * @apiName Add ResidentMedicalHistoryCondition
     * @apiGroup Admin Resident Medical History Conditions
     * @apiDescription This function is used to add residentMedicalHistoryCondition
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id           The unique identifier of the resident
     * @apiParam {Int}     condition_id          The unique identifier of the condition in select mode
     * @apiParam {Object}  condition             The new condition in add new mode
     * @apiParam {String}  date                  The date of the residentMedicalHistoryCondition
     * @apiParam {String}  [notes]               The notes of the residentMedicalHistoryCondition
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "condition_id": 1,
     *          "condition": {
     *                          "title": "Neuropathy",
     *                          "description": "some description"
     *                        },
     *          "date": "1929-11-12",
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
     *              "condition_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_resident_medical_history_condition_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_medical_history_condition", level="ADD")
     *
     * @param Request $request
     * @param ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService)
    {
        $id = $residentMedicalHistoryConditionService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'condition_id' => $request->get('condition_id'),
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
     * @api {put} /api/v1.0/admin/resident/history/medical/history/{id} Edit ResidentMedicalHistoryCondition
     * @apiVersion 1.0.0
     * @apiName Edit ResidentMedicalHistoryCondition
     * @apiGroup Admin Resident Medical History Conditions
     * @apiDescription This function is used to edit residentMedicalHistoryCondition
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id           The unique identifier of the resident
     * @apiParam {Int}     condition_id          The unique identifier of the condition in select mode
     * @apiParam {Object}  condition             The new condition in add new mode
     * @apiParam {String}  date                  The date of the residentMedicalHistoryCondition
     * @apiParam {String}  [notes]               The notes of the residentMedicalHistoryCondition
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "condition_id": 1,
     *          "condition": {
     *                          "title": "Neuropathy",
     *                          "description": "some description"
     *                        },
     *          "date": "1929-11-12",
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
     *              "condition_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medical_history_condition_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_medical_history_condition", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService)
    {
        $residentMedicalHistoryConditionService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'condition_id' => $request->get('condition_id'),
                'date' => $request->get('date'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/history/medical/history/{id} Delete ResidentMedicalHistoryCondition
     * @apiVersion 1.0.0
     * @apiName Delete ResidentMedicalHistoryCondition
     * @apiGroup Admin Resident Medical History Conditions
     * @apiDescription This function is used to remove residentMedicalHistoryCondition
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
     *          "error": "ResidentMedicalHistoryCondition not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medical_history_condition_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_medical_history_condition", level="DELETE")
     *
     * @param $id
     * @param ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService)
    {
        $residentMedicalHistoryConditionService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/history/medical/history Bulk Delete ResidentMedicalHistoryConditions
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ResidentMedicalHistoryConditions
     * @apiGroup Admin Resident Medical History Conditions
     * @apiDescription This function is used to bulk remove residentMedicalHistoryConditions
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the residentMedicalHistoryConditions
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
     *          "error": "ResidentMedicalHistoryCondition not found"
     *     }
     *
     * @Route("", name="api_admin_resident_medical_history_condition_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_medical_history_condition", level="DELETE")
     *
     * @param Request $request
     * @param ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService)
    {
        $residentMedicalHistoryConditionService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/history/medical/history/related/info ResidentMedicalHistoryCondition related info
     * @apiVersion 1.0.0
     * @apiName ResidentMedicalHistoryCondition Related Info
     * @apiGroup Admin Resident Medical History Conditions
     * @apiDescription This function is used to get residentMedicalHistoryCondition related info
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
     *          "error": "ResidentMedicalHistoryCondition not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_resident_medical_history_condition_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentMedicalHistoryConditionService $residentMedicalHistoryConditionService)
    {
        $relatedData = $residentMedicalHistoryConditionService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
