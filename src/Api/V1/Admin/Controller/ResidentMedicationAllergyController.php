<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentMedicationAllergyService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentMedicationAllergy;
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
 * @Route("/api/v1.0/admin/resident/history/allergy/medication")
 *
 * @Grant(grant="persistence-resident-resident_medication_allergy", level="VIEW")
 *
 * Class ResidentMedicationAllergyController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentMedicationAllergyController extends BaseController
{
    /**
     * @api {get} /api/v1.0/admin/resident/history/allergy/medication/grid Get ResidentMedicationAllergies Grid
     * @apiVersion 1.0.0
     * @apiName Get ResidentMedicationAllergies Grid
     * @apiGroup Admin Resident Medication Allergies
     * @apiDescription This function is used to listing residentMedicationAllergies
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentMedicationAllergy
     * @apiSuccess {Object}   resident             The resident of the residentMedicationAllergy
     * @apiSuccess {Object}   medication           The medication of the residentMedicationAllergy
     * @apiSuccess {String}   notes                The notes of the residentMedicationAllergy
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
     *                   "notes": "some notes"
     *              }
     *          ]
     *     }
     *
     * @Route("/grid", name="api_admin_resident_medication_allergy_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentMedicationAllergyService $residentMedicationAllergyService)
    {
        return $this->respondGrid(
            $request,
            ResidentMedicationAllergy::class,
            'api_admin_resident_medication_allergy_grid',
            $residentMedicationAllergyService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {options} /api/v1.0/admin/resident/history/allergy/medication/grid Get ResidentMedicationAllergy Grid Options
     * @apiVersion 1.0.0
     * @apiName Get ResidentMedicationAllergy Grid Options
     * @apiGroup Admin Resident Medication Allergies
     * @apiDescription This function is used to describe options of listing
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Array} options The options of the residentMedicationAllergy listing
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
     * @Route("/grid", name="api_admin_resident_medication_allergy_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentMedicationAllergy::class, 'api_admin_resident_medication_allergy_grid');
    }

    /**
     * @api {get} /api/v1.0/admin/resident/history/allergy/medication Get ResidentMedicationAllergies
     * @apiVersion 1.0.0
     * @apiName Get ResidentMedicationAllergies
     * @apiGroup Admin Resident Medication Allergies
     * @apiDescription This function is used to listing residentMedicationAllergies
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentMedicationAllergy
     * @apiSuccess {Object}   resident             The resident of the residentMedicationAllergy
     * @apiSuccess {Object}   medication           The medication of the residentMedicationAllergy
     * @apiSuccess {String}   notes                The notes of the residentMedicationAllergy
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
     *                  "medication": {
     *                      "id": 1,
     *                      "name": "Ampicillin"
     *                  },
     *                  "notes": "some notes"
     *              }
     *          ]
     *     }
     *
     * @Route("", name="api_admin_resident_medication_allergy_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentMedicationAllergyService $residentMedicationAllergyService)
    {
        return $this->respondList(
            $request,
            ResidentMedicationAllergy::class,
            'api_admin_resident_medication_allergy_list',
            $residentMedicationAllergyService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @api {get} /api/v1.0/admin/resident/history/allergy/medication/{id} Get ResidentMedicationAllergy
     * @apiVersion 1.0.0
     * @apiName Get ResidentMedicationAllergy
     * @apiGroup Admin Resident Medication Allergies
     * @apiDescription This function is used to get residentMedicationAllergy
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiSuccess {Int}      id                   The unique identifier of the residentMedicationAllergy
     * @apiSuccess {Object}   resident             The resident of the residentMedicationAllergy
     * @apiSuccess {Object}   medication           The medication of the residentMedicationAllergy
     * @apiSuccess {String}   notes                The notes of the residentMedicationAllergy
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 200 OK
     *     {
     *          "data": {
     *                  "id": 1,
     *                  "resident": {
     *                      "id": 1
     *                  },
     *                  "medication": {
     *                      "id": 1,
     *                      "name": "Ampicillin"
     *                  },
     *                  "notes": "some notes"
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medication_allergy_get", methods={"GET"})
     *
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentMedicationAllergyService $residentMedicationAllergyService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentMedicationAllergyService->getById($id),
            ['api_admin_resident_medication_allergy_get']
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/history/allergy/medication Add ResidentMedicationAllergy
     * @apiVersion 1.0.0
     * @apiName Add ResidentMedicationAllergy
     * @apiGroup Admin Resident Medication Allergies
     * @apiDescription This function is used to add residentMedicationAllergy
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id           The unique identifier of the resident
     * @apiParam {Int}     medication_id         The unique identifier of the medication in select mode
     * @apiParam {Object}  medication            The new medication in add new mode
     * @apiParam {String}  [notes]               The notes of the residentMedicationAllergy
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "medication_id": 1,
     *          "medication": {
     *                          "name": "Ampicillin"
     *                        },
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
     *              "medication_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("", name="api_admin_resident_medication_allergy_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_medication_allergy", level="ADD")
     *
     * @param Request $request
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse
     * @throws \Exception
     */
    public function addAction(Request $request, ResidentMedicationAllergyService $residentMedicationAllergyService)
    {
        $id = $residentMedicationAllergyService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'medication_id' => $request->get('medication_id'),
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
     * @api {put} /api/v1.0/admin/resident/history/allergy/medication/{id} Edit ResidentMedicationAllergy
     * @apiVersion 1.0.0
     * @apiName Edit ResidentMedicationAllergy
     * @apiGroup Admin Resident Medication Allergies
     * @apiDescription This function is used to edit residentMedicationAllergy
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int}     resident_id           The unique identifier of the resident
     * @apiParam {Int}     medication_id         The unique identifier of the medication in select mode
     * @apiParam {Object}  medication            The new medication in add new mode
     * @apiParam {String}  [notes]               The notes of the residentMedicationAllergy
     *
     * @apiParamExample {json} Request-Example:
     *     {
     *          "resident_id": 1,
     *          "medication_id": 1,
     *          "medication": {
     *                          "name": "Ampicillin"
     *                        },
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
     *              "medication_id": "Sorry, this value not be blank."
     *          }
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medication_allergy_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_medication_allergy", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ResidentMedicationAllergyService $residentMedicationAllergyService)
    {
        $residentMedicationAllergyService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'medication_id' => $request->get('medication_id'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/history/allergy/medication/{id} Delete ResidentMedicationAllergy
     * @apiVersion 1.0.0
     * @apiName Delete ResidentMedicationAllergy
     * @apiGroup Admin Resident Medication Allergies
     * @apiDescription This function is used to remove residentMedicationAllergy
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
     *          "error": "ResidentMedicationAllergy not found"
     *     }
     *
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medication_allergy_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_medication_allergy", level="DELETE")
     *
     * @param $id
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentMedicationAllergyService $residentMedicationAllergyService)
    {
        $residentMedicationAllergyService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {delete} /api/v1.0/admin/resident/history/allergy/medication Bulk Delete ResidentMedicationAllergies
     * @apiVersion 1.0.0
     * @apiName Bulk Delete ResidentMedicationAllergies
     * @apiGroup Admin Resident Medication Allergies
     * @apiDescription This function is used to bulk remove residentMedicationAllergies
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the residentMedicationAllergies
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
     *          "error": "ResidentMedicationAllergy not found"
     *     }
     *
     * @Route("", name="api_admin_resident_medication_allergy_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_medication_allergy", level="DELETE")
     *
     * @param Request $request
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentMedicationAllergyService $residentMedicationAllergyService)
    {
        $residentMedicationAllergyService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @api {post} /api/v1.0/admin/resident/history/allergy/medication/related/info ResidentMedicationAllergy related info
     * @apiVersion 1.0.0
     * @apiName ResidentMedicationAllergy Related Info
     * @apiGroup Admin Resident Medication Allergies
     * @apiDescription This function is used to get residentMedicationAllergy related info
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
     *          "error": "ResidentMedicationAllergy not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_resident_medication_allergy_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentMedicationAllergyService $residentMedicationAllergyService)
    {
        $relatedData = $residentMedicationAllergyService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
