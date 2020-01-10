<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\MedicationService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Medication;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/medication")
 *
 * @Grant(grant="persistence-common-medication", level="VIEW")
 *
 * Class MedicationController
 * @package App\Api\V1\Admin\Controller
 */
class MedicationController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_medication_grid", methods={"GET"})
     *
     * @param Request $request
     * @param MedicationService $medicationService
     * @return JsonResponse
     */
    public function gridAction(Request $request, MedicationService $medicationService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Medication::class,
            'api_admin_medication_grid',
            $medicationService
        );
    }

    /**
     * @Route("/grid", name="api_admin_medication_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Medication::class, 'api_admin_medication_grid');
    }

    /**
     * @Route("", name="api_admin_medication_list", methods={"GET"})
     *
     * @param Request $request
     * @param MedicationService $medicationService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, MedicationService $medicationService)
    {
        return $this->respondList(
            $request,
            Medication::class,
            'api_admin_medication_list',
            $medicationService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medication_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param MedicationService $medicationService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, MedicationService $medicationService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $medicationService->getById($id),
            ['api_admin_medication_get']
        );
    }

    /**
     * @Route("", name="api_admin_medication_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-medication", level="ADD")
     *
     * @param Request $request
     * @param MedicationService $medicationService
     * @return JsonResponse
     */
    public function addAction(Request $request, MedicationService $medicationService): JsonResponse
    {
        $id = $medicationService->add(
            [
                'title' => $request->get('title'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medication_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-medication", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param MedicationService $medicationService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, MedicationService $medicationService): JsonResponse
    {
        $medicationService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medication_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-medication", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param MedicationService $medicationService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, MedicationService $medicationService): JsonResponse
    {
        $medicationService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_medication_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-medication", level="DELETE")
     *
     * @param Request $request
     * @param MedicationService $medicationService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, MedicationService $medicationService): JsonResponse
    {
        $medicationService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_medication_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param MedicationService $medicationService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, MedicationService $medicationService): JsonResponse
    {
        $relatedData = $medicationService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
