<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentMedicationAllergyService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentMedicationAllergy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
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
     * @Route("/grid", name="api_admin_resident_medication_allergy_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentMedicationAllergyService $residentMedicationAllergyService): JsonResponse
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
     * @Route("/grid", name="api_admin_resident_medication_allergy_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentMedicationAllergy::class, 'api_admin_resident_medication_allergy_grid');
    }

    /**
     * @Route("", name="api_admin_resident_medication_allergy_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return PdfResponse|JsonResponse|Response
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medication_allergy_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentMedicationAllergyService $residentMedicationAllergyService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentMedicationAllergyService->getById($id),
            ['api_admin_resident_medication_allergy_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_medication_allergy_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_medication_allergy", level="ADD")
     *
     * @param Request $request
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentMedicationAllergyService $residentMedicationAllergyService): JsonResponse
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medication_allergy_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_medication_allergy", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentMedicationAllergyService $residentMedicationAllergyService): JsonResponse
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_medication_allergy_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_medication_allergy", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentMedicationAllergyService $residentMedicationAllergyService): JsonResponse
    {
        $residentMedicationAllergyService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_medication_allergy_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_medication_allergy", level="DELETE")
     *
     * @param Request $request
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentMedicationAllergyService $residentMedicationAllergyService): JsonResponse
    {
        $residentMedicationAllergyService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_medication_allergy_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentMedicationAllergyService $residentMedicationAllergyService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentMedicationAllergyService $residentMedicationAllergyService): JsonResponse
    {
        $relatedData = $residentMedicationAllergyService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
