<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentDiagnosisService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentDiagnosis;
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
 * @Route("/api/v1.0/admin/resident/history/diagnose")
 *
 * @Grant(grant="persistence-resident-resident_diagnosis", level="VIEW")
 *
 * Class ResidentDiagnosisController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentDiagnosisController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/history/diagnose/grid Get ResidentDiagnoses Grid
     * @apiVersion 1.0.0
     * @apiName Get ResidentDiagnoses Grid
     * @apiGroup Admin Resident Diagnoses
     * @apiDescription This function is used to listing residentDiagnoses
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentDiagnosis
     * @apiSuccess {Object}   resident             The resident of the residentDiagnosis
     * @apiSuccess {Object}   diagnosis            The diagnosis of the residentDiagnosis
     * @apiSuccess {Int}      type                 The type of the residentDiagnosis
     * @apiSuccess {String}   notes                The notes of the residentDiagnosis
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
     *                   "diagnosis": "Failure to Thrive",
     *                   "type": 1,
     *                   "notes": "some notes"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_diagnosis_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentDiagnosisService $residentDiagnosisService)
    {
        return $this->respondGrid(
            $request,
            ResidentDiagnosis::class,
            'api_admin_resident_diagnosis_grid',
            $residentDiagnosisService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/history/diagnose/grid Get ResidentDiagnosis Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ResidentDiagnosis Grid Options
     * @apiGroup Admin Resident Diagnoses
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the residentDiagnosis listing
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
     * @Route("/grid", name="api_admin_resident_diagnosis_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentDiagnosis::class, 'api_admin_resident_diagnosis_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/history/diagnose Get ResidentDiagnoses
     * @apiVersion 1.0.0
     * @apiName Get ResidentDiagnoses
     * @apiGroup Admin Resident Diagnoses
     * @apiDescription This function is used to listing residentDiagnoses
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentDiagnosis
     * @apiSuccess {Object}   resident             The resident of the residentDiagnosis
     * @apiSuccess {Object}   diagnosis            The diagnosis of the residentDiagnosis
     * @apiSuccess {Int}      type                 The type of the residentDiagnosis
     * @apiSuccess {String}   notes                The notes of the residentDiagnosis
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
     *                  "diagnosis": {
     *                          "title": "Failure to Thrive",
     *                          "acronym": "FTT",
     *                          "description": "some description"
     *                  },
     *                  "type": 1,
     *                  "notes": "some notes"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_resident_diagnosis_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentDiagnosisService $residentDiagnosisService)
    {
        return $this->respondList(
            $request,
            ResidentDiagnosis::class,
            'api_admin_resident_diagnosis_list',
            $residentDiagnosisService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/history/diagnose/{id} Get ResidentDiagnosis
     * @apiVersion 1.0.0
     * @apiName Get ResidentDiagnosis
     * @apiGroup Admin Resident Diagnoses
     * @apiDescription This function is used to get residentDiagnosis
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentDiagnosis
     * @apiSuccess {Object}   resident             The resident of the residentDiagnosis
     * @apiSuccess {Object}   diagnosis            The diagnosis of the residentDiagnosis
     * @apiSuccess {Int}      type                 The type of the residentDiagnosis
     * @apiSuccess {String}   notes                The notes of the residentDiagnosis
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "diagnosis": {
     *                          "title": "Failure to Thrive",
     *                          "acronym": "FTT",
     *                          "description": "some description"
     *                  },
     *                  "type": 1,
     *                  "notes": "some notes"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_diagnosis_get", methods={"GET"})
     *
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentDiagnosisService $residentDiagnosisService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentDiagnosisService->getById($id),
            ['api_admin_resident_diagnosis_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/history/diagnose Add ResidentDiagnosis
     * @apiVersion 1.0.0
     * @apiName Add ResidentDiagnosis
     * @apiGroup Admin Resident Diagnoses
     * @apiDescription This function is used to add residentDiagnosis
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id           The unique identifier of the resident
     * @apiParam {Int}     diagnosis_id          The unique identifier of the diagnosis in select mode
     * @apiParam {Object}  diagnosis             The new diagnosis in add new mode
     * @apiParam {Int}     type                  The type of the residentDiagnosis
     * @apiParam {String}  [notes]               The notes of the residentDiagnosis
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "diagnosis_id": 1,
     *          "diagnosis": {
     *                          "title": "Failure to Thrive",
     *                          "acronym": "FTT",
     *                          "description": "some description"
     *                        },
     *          "type": 1,
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
     *              "diagnosis_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_resident_diagnosis_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_diagnosis", level="ADD")
     *
     * @param Request $request
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResidentDiagnosisService $residentDiagnosisService)
    {
        $residentDiagnosisService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'diagnosis_id' => $request->get('diagnosis_id'),
                'diagnosis' => $request->get('diagnosis'),
                'type' => $request->get('type'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/history/diagnose/{id} Edit ResidentDiagnosis
     * @apiVersion 1.0.0
     * @apiName Edit ResidentDiagnosis
     * @apiGroup Admin Resident Diagnoses
     * @apiDescription This function is used to edit residentDiagnosis
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id           The unique identifier of the resident
     * @apiParam {Int}     diagnosis_id           The unique identifier of the diagnosis in select mode
     * @apiParam {Object}  diagnosis              The new diagnosis in add new mode
     * @apiParam {Int}     type                  The type of the residentDiagnosis
     * @apiParam {String}  [notes]               The notes of the residentDiagnosis
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "diagnosis_id": 1,
     *          "diagnosis": {
     *                          "title": "Failure to Thrive",
     *                          "acronym": "FTT",
     *                          "description": "some description"
     *                        },
     *          "type": 1,
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
     *              "diagnosis_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_diagnosis_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_diagnosis", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ResidentDiagnosisService $residentDiagnosisService)
    {
        $residentDiagnosisService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'diagnosis_id' => $request->get('diagnosis_id'),
                'diagnosis' => $request->get('diagnosis'),
                'type' => $request->get('type'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/history/diagnose/{id} Delete ResidentDiagnosis
     * @apiVersion 1.0.0
     * @apiName Delete ResidentDiagnosis
     * @apiGroup Admin Resident Diagnoses
     * @apiDescription This function is used to remove residentDiagnosis
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
     *          "error": "ResidentDiagnosis not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_diagnosis_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_diagnosis", level="DELETE")
     *
     * @param $id
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentDiagnosisService $residentDiagnosisService)
    {
        $residentDiagnosisService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/history/diagnose Bulk Delete ResidentDiagnoses
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ResidentDiagnoses
     * @apiGroup Admin Resident Diagnoses
     * @apiDescription This function is used to bulk remove residentDiagnoses
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the residentDiagnoses
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
     *          "error": "ResidentDiagnosis not found"
     *     }
     *
     * @Route("", name="api_admin_resident_diagnosis_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_diagnosis", level="DELETE")
     *
     * @param Request $request
     * @param ResidentDiagnosisService $residentDiagnosisService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentDiagnosisService $residentDiagnosisService)
    {
        $residentDiagnosisService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }
}
