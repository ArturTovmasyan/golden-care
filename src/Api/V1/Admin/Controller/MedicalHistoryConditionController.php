<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\MedicalHistoryConditionService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\MedicalHistoryCondition;
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
 * @Route("/api/v1.0/admin/medical/history/condition")
 *
 * @Grant(grant="persistence-common-medical_history_condition", level="VIEW")
 *
 * Class MedicalHistoryConditionController
 * @package App\Api\V1\Admin\Controller
 */
class MedicalHistoryConditionController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/medical/history/condition/grid Get MedicalHistoryConditions Grid
     * @apiVersion 1.0.0
     * @apiName Get MedicalHistoryConditions Grid
     * @apiGroup Admin Medical History Conditions
     * @apiDescription This function is used to listing medicalHistoryConditions
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the medicalHistoryCondition
     * @apiSuccess {String}  title         The title of the medicalHistoryCondition
     * @apiSuccess {String}  description   The description time of the medicalHistoryCondition
     * @apiSuccess {Object}  space         The space of the medicalHistoryCondition
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
     *                  "title": "Acetylsalicylic Acid",
     *                  "description": "ASA",
     *                  "space": "alms"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_medical_history_condition_grid", methods={"GET"})
     *
     * @param Request $request
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, MedicalHistoryConditionService $medicalHistoryConditionService)
    {
        return $this->respondGrid(
            $request,
            MedicalHistoryCondition::class,
            'api_admin_medical_history_condition_grid',
            $medicalHistoryConditionService
        );
    }

    /**
     * @api {options} /api/v1.0/admin/medical/history/condition/grid Get MedicalHistoryCondition Grid Options
     * @apiVersion 1.0.0
     * @apiName Get MedicalHistoryCondition Grid Options
     * @apiGroup Admin Medical History Conditions
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the medicalHistoryCondition listing
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
     * @Route("/grid", name="api_admin_medical_history_condition_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, MedicalHistoryCondition::class, 'api_admin_medical_history_condition_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/medical/history/condition Get MedicalHistoryConditions
     * @apiVersion 1.0.0
     * @apiName Get MedicalHistoryConditions
     * @apiGroup Admin Medical History Conditions
     * @apiDescription This function is used to listing medicalHistoryConditions
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the medicalHistoryCondition
     * @apiSuccess {String}  title         The title of the medicalHistoryCondition
     * @apiSuccess {String}  description   The description time of the medicalHistoryCondition
     * @apiSuccess {Object}  space         The space of the medicalHistoryCondition
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
     *                  "title": "Acetylsalicylic Acid",
     *                  "description": "ASA",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_medical_history_condition_list", methods={"GET"})
     *
     * @param Request $request
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, MedicalHistoryConditionService $medicalHistoryConditionService)
    {
        return $this->respondList(
            $request,
            MedicalHistoryCondition::class,
            'api_admin_medical_history_condition_list',
            $medicalHistoryConditionService
        );
    }

    /**
     * @api {get} /api/v1.0/admin/medical/history/condition/{id} Get MedicalHistoryCondition
     * @apiVersion 1.0.0
     * @apiName Get MedicalHistoryCondition
     * @apiGroup Admin Medical History Conditions
     * @apiDescription This function is used to get medicalHistoryCondition
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}     id            The unique identifier of the medicalHistoryCondition
     * @apiSuccess {String}  title         The title of the medicalHistoryCondition
     * @apiSuccess {String}  description   The description time of the medicalHistoryCondition
     * @apiSuccess {Object}  space         The space of the medicalHistoryCondition
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "title": "Acetylsalicylic Acid",
     *                  "description": "ASA",
     *                  "space": {
     *                      "id": 1,
     *                      "name": "alms"
     *                  }
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medical_history_condition_get", methods={"GET"})
     *
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, MedicalHistoryConditionService $medicalHistoryConditionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $medicalHistoryConditionService->getById($id),
            ['api_admin_medical_history_condition_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/medical/history/condition Add MedicalHistoryCondition
     * @apiVersion 1.0.0
     * @apiName Add MedicalHistoryCondition
     * @apiGroup Admin Medical History Conditions
     * @apiDescription This function is used to add medicalHistoryCondition
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title           The title of the medicalHistoryCondition
     * @apiParam {String}  [description]   The description of the medicalHistoryCondition
     * @apiParam {Int}     space_id        The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Acetylsalicylic Acid",
     *         "description": "ASA",
     *         "space_id": 1
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
     * @Route("", name="api_admin_medical_history_condition_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-medical_history_condition", level="ADD")
     *
     * @param Request $request
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, MedicalHistoryConditionService $medicalHistoryConditionService)
    {
        $id = $medicalHistoryConditionService->add(
            [
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
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
     * @api {put} /api/v1.0/admin/medical/history/condition/{id} Edit MedicalHistoryCondition
     * @apiVersion 1.0.0
     * @apiName Edit MedicalHistoryCondition
     * @apiGroup Admin Medical History Conditions
     * @apiDescription This function is used to edit medicalHistoryCondition
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  title          The title of the medicalHistoryCondition
     * @apiParam {String}  [description]  The description of the medicalHistoryCondition
     * @apiParam {Int}     space_id       The unique identifier of the space
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *         "title": "Acetylsalicylic Acid",
     *         "description": "ASA",
     *         "space_id": 1
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
     *              "name": "Sorry, this title is already in use."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medical_history_condition_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-medical_history_condition", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, MedicalHistoryConditionService $medicalHistoryConditionService)
    {
        $medicalHistoryConditionService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'description' => $request->get('description') ?? '',
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/medical/history/condition/{id} Delete MedicalHistoryCondition
     * @apiVersion 1.0.0
     * @apiName Delete MedicalHistoryCondition
     * @apiGroup Admin Medical History Conditions
     * @apiDescription This function is used to remove medicalHistoryCondition
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
     *          "error": "MedicalHistoryCondition not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medical_history_condition_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-medical_history_condition", level="DELETE")
     *
     * @param $id
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, MedicalHistoryConditionService $medicalHistoryConditionService)
    {
        $medicalHistoryConditionService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/medical/history/condition Bulk Delete MedicalHistoryConditions
     * @apiVersion 1.0.0
     * @apiName Bulk Delete MedicalHistoryConditions
     * @apiGroup Admin Medical History Conditions
     * @apiDescription This function is used to bulk remove medicalHistoryConditions
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the medicalHistoryConditions
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
     *          "error": "MedicalHistoryCondition not found"
     *     }
     *
     * @Route("", name="api_admin_medical_history_condition_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-medical_history_condition", level="DELETE")
     *
     * @param Request $request
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, MedicalHistoryConditionService $medicalHistoryConditionService)
    {
        $medicalHistoryConditionService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/medical/history/condition/related/info MedicalHistoryCondition related info
     * @apiVersion 1.0.0
     * @apiName MedicalHistoryCondition Related Info
     * @apiGroup Admin Medical History Conditions
     * @apiDescription This function is used to get medicalHistoryCondition related info
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
     *          "error": "MedicalHistoryCondition not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_medical_history_condition_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, MedicalHistoryConditionService $medicalHistoryConditionService)
    {
        $relatedData = $medicalHistoryConditionService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
