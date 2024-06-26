<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\MedicalHistoryConditionService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\MedicalHistoryCondition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
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
     * @Route("/grid", name="api_admin_medical_history_condition_grid", methods={"GET"})
     *
     * @param Request $request
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse
     */
    public function gridAction(Request $request, MedicalHistoryConditionService $medicalHistoryConditionService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            MedicalHistoryCondition::class,
            'api_admin_medical_history_condition_grid',
            $medicalHistoryConditionService
        );
    }

    /**
     * @Route("/grid", name="api_admin_medical_history_condition_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, MedicalHistoryCondition::class, 'api_admin_medical_history_condition_grid');
    }

    /**
     * @Route("", name="api_admin_medical_history_condition_list", methods={"GET"})
     *
     * @param Request $request
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return PdfResponse|JsonResponse|Response
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medical_history_condition_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, MedicalHistoryConditionService $medicalHistoryConditionService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $medicalHistoryConditionService->getById($id),
            ['api_admin_medical_history_condition_get']
        );
    }

    /**
     * @Route("", name="api_admin_medical_history_condition_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-medical_history_condition", level="ADD")
     *
     * @param Request $request
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse
     */
    public function addAction(Request $request, MedicalHistoryConditionService $medicalHistoryConditionService): JsonResponse
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medical_history_condition_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-medical_history_condition", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, MedicalHistoryConditionService $medicalHistoryConditionService): JsonResponse
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_medical_history_condition_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-medical_history_condition", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, MedicalHistoryConditionService $medicalHistoryConditionService): JsonResponse
    {
        $medicalHistoryConditionService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_medical_history_condition_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-medical_history_condition", level="DELETE")
     *
     * @param Request $request
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, MedicalHistoryConditionService $medicalHistoryConditionService): JsonResponse
    {
        $medicalHistoryConditionService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_medical_history_condition_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param MedicalHistoryConditionService $medicalHistoryConditionService
     * @return JsonResponse'
     */
    public function relatedInfoAction(Request $request, MedicalHistoryConditionService $medicalHistoryConditionService): JsonResponse
    {
        $relatedData = $medicalHistoryConditionService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
