<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentMedicationService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentMedication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
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
     * @Route("/grid", name="api_admin_resident_medication_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentMedicationService $residentMedicationService): JsonResponse
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
     * @Route("/grid", name="api_admin_resident_medication_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentMedication::class, 'api_admin_resident_medication_grid');
    }

    /**
     * @Route("", name="api_admin_resident_medication_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentMedicationService $residentMedicationService
     * @return PdfResponse|JsonResponse|Response
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medication_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentMedicationService $residentMedicationService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentMedicationService->getById($id),
            ['api_admin_resident_medication_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_medication_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_medication", level="ADD")
     *
     * @param Request $request
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentMedicationService $residentMedicationService): JsonResponse
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medication_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_medication", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentMedicationService $residentMedicationService): JsonResponse
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medication_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_medication", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentMedicationService $residentMedicationService): JsonResponse
    {
        $residentMedicationService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_medication_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_medication", level="DELETE")
     *
     * @param Request $request
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentMedicationService $residentMedicationService): JsonResponse
    {
        $residentMedicationService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_medication_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentMedicationService $residentMedicationService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentMedicationService $residentMedicationService): JsonResponse
    {
        $relatedData = $residentMedicationService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
