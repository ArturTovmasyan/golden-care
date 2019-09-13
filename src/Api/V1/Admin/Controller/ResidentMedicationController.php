<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentMedicationService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentMedication;
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
 * @Route("/api/v1.0/admin/resident/medication")
 *
 * @Grant(grant="persistence-resident-resident_medication", level="VIEW")
 *
 * Class ResidentMedicationController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentMedicationController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/medication/grid Get ResidentMedications Grid
     * @apiVersion 1.0.0
     * @apiName Get ResidentMedications Grid
     * @apiGroup Admin Resident Medications
     * @apiDescription This function is used to listing residentMedications
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentMedication
     * @apiSuccess {Object}   resident             The resident of the residentMedication
     * @apiSuccess {Object}   physician            The physician of the residentMedication
     * @apiSuccess {Object}   medication           The medication of the residentMedication
     * @apiSuccess {Object}   form_factor          The formFactor of the residentMedication
     * @apiSuccess {String}   dosage               The dosage of the residentMedication
     * @apiSuccess {String}   dosage_unit          The dosageUnit of the residentMedication
     * @apiSuccess {String}   am                   The am of the residentMedication
     * @apiSuccess {String}   nn                   The nn of the residentMedication
     * @apiSuccess {String}   pm                   The pm of the residentMedication
     * @apiSuccess {String}   hs                   The hs of the residentMedication
     * @apiSuccess {Boolean}  prn                  The prn status of the residentMedication
     * @apiSuccess {Boolean}  discontinued         The discontinued status of the residentMedication
     * @apiSuccess {Boolean}  treatment            The treatment status of the residentMedication
     * @apiSuccess {String}   notes                The notes of the residentMedication
     * @apiSuccess {String}   prescription_number  The prescriptionNumber of the residentMedication
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
     *                   "medication": "Ampicillin",
     *                   "form_factor": "Factor1",
     *                   "dosage": "30",
     *                   "dosage_unit": "mg",
     *                   "am": "0",
     *                   "nn": "0",
     *                   "pm": "0",
     *                   "hs": "0",
     *                   "prn": false,
     *                   "discontinued": false,
     *                   "treatment": false,
     *                   "notes": "some notes",
     *                   "prescription_number": "OTC"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_medication_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentMedicationService $residentMedicationService)
    {
        return $this->respondGrid(
            $request,
            ResidentMedication::class,
            'api_admin_resident_medication_grid',
            $residentMedicationService,
            [
                'resident_id' => $request->get('resident_id'),
                'discontinued' => $request->get('discontinued')
            ]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/medication/grid Get ResidentMedication Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ResidentMedication Grid Options
     * @apiGroup Admin Resident Medications
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the residentMedication listing
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
     * @Route("/grid", name="api_admin_resident_medication_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentMedication::class, 'api_admin_resident_medication_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/medication Get ResidentMedications
     * @apiVersion 1.0.0
     * @apiName Get ResidentMedications
     * @apiGroup Admin Resident Medications
     * @apiDescription This function is used to listing residentMedications
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentMedication
     * @apiSuccess {Object}   resident             The resident of the residentMedication
     * @apiSuccess {Object}   physician            The physician of the residentMedication
     * @apiSuccess {Object}   medication           The medication of the residentMedication
     * @apiSuccess {Object}   form_factor          The formFactor of the residentMedication
     * @apiSuccess {String}   dosage               The dosage of the residentMedication
     * @apiSuccess {String}   dosage_unit          The dosageUnit of the residentMedication
     * @apiSuccess {String}   am                   The am of the residentMedication
     * @apiSuccess {String}   nn                   The nn of the residentMedication
     * @apiSuccess {String}   pm                   The pm of the residentMedication
     * @apiSuccess {String}   hs                   The hs of the residentMedication
     * @apiSuccess {Boolean}  prn                  The prn status of the residentMedication
     * @apiSuccess {Boolean}  discontinued         The discontinued status of the residentMedication
     * @apiSuccess {Boolean}  treatment            The treatment status of the residentMedication
     * @apiSuccess {String}   notes                The notes of the residentMedication
     * @apiSuccess {String}   prescription_number  The prescriptionNumber of the residentMedication
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
     *                  "physician": {
     *                      "id": 1
     *                  },
     *                  "medication": {
     *                      "id": 1,
     *                      "name": "Ampicillin"
     *                  },
     *                  "form_factor": {
     *                      "id": 1,
     *                      "title": "Factor1"
     *                  },
     *                  "dosage": "30",
     *                  "dosage_unit": "mg",
     *                  "am": "0",
     *                  "nn": "0",
     *                  "pm": "0",
     *                  "hs": "0",
     *                  "prn": false,
     *                  "discontinued": false,
     *                  "treatment": false,
     *                  "notes": "some notes",
     *                  "prescription_number": "OTC"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_resident_medication_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentMedicationService $residentMedicationService)
    {
        return $this->respondList(
            $request,
            ResidentMedication::class,
            'api_admin_resident_medication_list',
            $residentMedicationService,
            [
                'resident_id' => $request->get('resident_id'),
                'medication_id' => $request->get('medication_id'),
                'discontinued' => $request->get('discontinued')
            ]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/medication/{id} Get ResidentMedication
     * @apiVersion 1.0.0
     * @apiName Get ResidentMedication
     * @apiGroup Admin Resident Medications
     * @apiDescription This function is used to get residentMedication
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentMedication
     * @apiSuccess {Object}   resident             The resident of the residentMedication
     * @apiSuccess {Object}   physician            The physician of the residentMedication
     * @apiSuccess {Object}   medication           The medication of the residentMedication
     * @apiSuccess {Object}   form_factor          The formFactor of the residentMedication
     * @apiSuccess {String}   dosage               The dosage of the residentMedication
     * @apiSuccess {String}   dosage_unit          The dosageUnit of the residentMedication
     * @apiSuccess {String}   am                   The am of the residentMedication
     * @apiSuccess {String}   nn                   The nn of the residentMedication
     * @apiSuccess {String}   pm                   The pm of the residentMedication
     * @apiSuccess {String}   hs                   The hs of the residentMedication
     * @apiSuccess {Boolean}  prn                  The prn status of the residentMedication
     * @apiSuccess {Boolean}  discontinued         The discontinued status of the residentMedication
     * @apiSuccess {Boolean}  treatment            The treatment status of the residentMedication
     * @apiSuccess {String}   notes                The notes of the residentMedication
     * @apiSuccess {String}   prescription_number  The prescriptionNumber of the residentMedication
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "physician": {
     *                      "id": 1
     *                  },
     *                  "medication": {
     *                      "id": 1,
     *                      "name": "Ampicillin"
     *                  },
     *                  "form_factor": {
     *                      "id": 1,
     *                      "title": "Factor1"
     *                  },
     *                  "dosage": "30",
     *                  "dosage_unit": "mg",
     *                  "am": "0",
     *                  "nn": "0",
     *                  "pm": "0",
     *                  "hs": "0",
     *                  "prn": false,
     *                  "discontinued": false,
     *                  "treatment": false,
     *                  "notes": "some notes",
     *                  "prescription_number": "OTC"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medication_get", methods={"GET"})
     *
     * @param ResidentMedicationService $residentMedicationService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentMedicationService $residentMedicationService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentMedicationService->getById($id),
            ['api_admin_resident_medication_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/medication Add ResidentMedication
     * @apiVersion 1.0.0
     * @apiName Add ResidentMedication
     * @apiGroup Admin Resident Medications
     * @apiDescription This function is used to add residentMedication
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id           The unique identifier of the resident
     * @apiParam {Int}     physician_id          The unique identifier of the physician
     * @apiParam {Int}     medication_id         The unique identifier of the medication
     * @apiParam {Int}     form_factor_id        The unique identifier of the formFactor
     * @apiParam {String}  dosage                The dosage of the residentMedication
     * @apiParam {String}  dosage_unit           The dosageUnit of the residentMedication
     * @apiParam {String}  [am]                  The am of the residentMedication
     * @apiParam {String}  [nn]                  The nn of the residentMedication
     * @apiParam {String}  [pm]                  The pm of the residentMedication
     * @apiParam {String}  [hs]                  The hs of the residentMedication
     * @apiParam {Int}     prn                   The prn status of the residentMedication
     * @apiParam {Int}     discontinued          The discontinued status of the residentMedication
     * @apiParam {Int}     treatment             The treatment status of the residentMedication
     * @apiParam {String}  [notes]               The notes of the residentMedication
     * @apiParam {String}  [prescription_number] The prescriptionNumber of the residentMedication
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "physician_id": 1,
     *          "medication_id": 1,
     *          "form_factor_id": 1,
     *          "dosage": "30",
     *          "dosage_unit": "mg",
     *          "am": "0",
     *          "nn": "0",
     *          "pm": "0",
     *          "hs": "0",
     *          "prn": false,
     *          "discontinued": false,
     *          "treatment": false,
     *          "notes": "some notes",
     *          "prescription_number": "OTC"
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
     *              "dosage": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_resident_medication_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_medication", level="ADD")
     *
     * @param Request $request
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ResidentMedicationService $residentMedicationService)
    {
        $id = $residentMedicationService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'physician_id' => $request->get('physician_id'),
                'medication_id' => $request->get('medication_id'),
                'form_factor_id' => $request->get('form_factor_id'),
                'dosage' => $request->get('dosage'),
                'dosage_unit' => $request->get('dosage_unit'),
                'am' => $request->get('am') ?? '0',
                'nn' => $request->get('nn') ?? '0',
                'pm' => $request->get('pm') ?? '0',
                'hs' => $request->get('hs') ?? '0',
                'prn' => $request->get('prn'),
                'discontinued' => $request->get('discontinued'),
                'treatment' => $request->get('treatment'),
                'notes' => $request->get('notes') ?? '',
                'prescription_number' => $request->get('prescription_number') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @api {put} /api/v1.0/admin/resident/medication/{id} Edit ResidentMedication
     * @apiVersion 1.0.0
     * @apiName Edit ResidentMedication
     * @apiGroup Admin Resident Medications
     * @apiDescription This function is used to edit residentMedication
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id           The unique identifier of the resident
     * @apiParam {Int}     physician_id          The unique identifier of the physician
     * @apiParam {Int}     medication_id         The unique identifier of the medication
     * @apiParam {Int}     form_factor_id        The unique identifier of the formFactor
     * @apiParam {String}  dosage                The dosage of the residentMedication
     * @apiParam {String}  dosage_unit           The dosageUnit of the residentMedication
     * @apiParam {String}  [am]                  The am of the residentMedication
     * @apiParam {String}  [nn]                  The nn of the residentMedication
     * @apiParam {String}  [pm]                  The pm of the residentMedication
     * @apiParam {String}  [hs]                  The hs of the residentMedication
     * @apiParam {Int}     prn                   The prn status of the residentMedication
     * @apiParam {Int}     discontinued          The discontinued status of the residentMedication
     * @apiParam {Int}     treatment             The treatment status of the residentMedication
     * @apiParam {String}  [notes]               The notes of the residentMedication
     * @apiParam {String}  [prescription_number] The prescriptionNumber of the residentMedication
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "physician_id": 1,
     *          "medication_id": 1,
     *          "form_factor_id": 1,
     *          "dosage": "30",
     *          "dosage_unit": "mg",
     *          "am": "0",
     *          "nn": "0",
     *          "pm": "0",
     *          "hs": "0",
     *          "prn": false,
     *          "discontinued": false,
     *          "treatment": false,
     *          "notes": "some notes",
     *          "prescription_number": "OTC"
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
     *              "dosage": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medication_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_medication", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResidentMedicationService $residentMedicationService)
    {
        $residentMedicationService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'physician_id' => $request->get('physician_id'),
                'medication_id' => $request->get('medication_id'),
                'form_factor_id' => $request->get('form_factor_id'),
                'dosage' => $request->get('dosage'),
                'dosage_unit' => $request->get('dosage_unit'),
                'am' => $request->get('am') ?? '0',
                'nn' => $request->get('nn') ?? '0',
                'pm' => $request->get('pm') ?? '0',
                'hs' => $request->get('hs') ?? '0',
                'prn' => $request->get('prn'),
                'discontinued' => $request->get('discontinued'),
                'treatment' => $request->get('treatment'),
                'notes' => $request->get('notes') ?? '',
                'prescription_number' => $request->get('prescription_number') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/medication/{id} Delete ResidentMedication
     * @apiVersion 1.0.0
     * @apiName Delete ResidentMedication
     * @apiGroup Admin Resident Medications
     * @apiDescription This function is used to remove residentMedication
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
     *          "error": "ResidentMedication not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medication_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_medication", level="DELETE")
     *
     * @param $id
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentMedicationService $residentMedicationService)
    {
        $residentMedicationService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/medication Bulk Delete ResidentMedications
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ResidentMedications
     * @apiGroup Admin Resident Medications
     * @apiDescription This function is used to bulk remove residentMedications
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the residentMedications
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
     *          "error": "ResidentMedication not found"
     *     }
     *
     * @Route("", name="api_admin_resident_medication_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_medication", level="DELETE")
     *
     * @param Request $request
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentMedicationService $residentMedicationService)
    {
        $residentMedicationService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/medication/related/info ResidentMedication related info
     * @apiVersion 1.0.0
     * @apiName ResidentMedication Related Info
     * @apiGroup Admin Resident Medications
     * @apiDescription This function is used to get residentMedication related info
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
     *          "error": "ResidentMedication not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_resident_medication_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentMedicationService $residentMedicationService)
    {
        $relatedData = $residentMedicationService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
