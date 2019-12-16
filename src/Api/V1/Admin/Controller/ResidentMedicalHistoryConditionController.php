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
     * @Route("/grid", name="api_admin_resident_medical_history_condition_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, ResidentMedicalHistoryCondition::class, 'api_admin_resident_medical_history_condition_grid');
    }

    /**
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
