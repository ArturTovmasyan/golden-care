<?php
namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Lead\Service\StageChangeReasonService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\StageChangeReason;
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
 * @Route("/api/v1.0/lead/stage-change-reason")
 *
 * @Grant(grant="persistence-lead-stage_change_reason", level="VIEW")
 *
 * Class StageChangeReasonController
 * @package App\Api\V1\Admin\Controller
 */
class StageChangeReasonController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_stage_change_reason", methods={"GET"})
     *
     * @param Request $request
     * @param StageChangeReasonService $stageChangeReasonService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, StageChangeReasonService $stageChangeReasonService)
    {
        return $this->respondGrid(
            $request,
            StageChangeReason::class,
            'api_lead_stage_change_reason_grid',
            $stageChangeReasonService
        );
    }

    /**
     * @Route("/grid", name="api_lead_stage_change_reason_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, StageChangeReason::class, 'api_lead_stage_change_reason_grid');
    }

    /**
     * @Route("", name="api_lead_stage_change_reason_list", methods={"GET"})
     *
     * @param Request $request
     * @param StageChangeReasonService $stageChangeReasonService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, StageChangeReasonService $stageChangeReasonService)
    {
        return $this->respondList(
            $request,
            StageChangeReason::class,
            'api_lead_stage_change_reason_list',
            $stageChangeReasonService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_stage_change_reason_get", methods={"GET"})
     *
     * @param StageChangeReasonService $stageChangeReasonService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, StageChangeReasonService $stageChangeReasonService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $stageChangeReasonService->getById($id),
            ['api_lead_stage_change_reason_get']
        );
    }

    /**
     * @Route("", name="api_lead_stage_change_reason_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-stage_change_reason", level="ADD")
     *
     * @param Request $request
     * @param StageChangeReasonService $stageChangeReasonService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, StageChangeReasonService $stageChangeReasonService)
    {
        $id = $stageChangeReasonService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_stage_change_reason_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-stage_change_reason", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param StageChangeReasonService $stageChangeReasonService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, StageChangeReasonService $stageChangeReasonService)
    {
        $stageChangeReasonService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_stage_change_reason_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-stage_change_reason", level="DELETE")
     *
     * @param $id
     * @param StageChangeReasonService $stageChangeReasonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, StageChangeReasonService $stageChangeReasonService)
    {
        $stageChangeReasonService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_stage_change_reason_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-stage_change_reason", level="DELETE")
     *
     * @param Request $request
     * @param StageChangeReasonService $stageChangeReasonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, StageChangeReasonService $stageChangeReasonService)
    {
        $stageChangeReasonService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_stage_change_reason_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param StageChangeReasonService $stageChangeReasonService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, StageChangeReasonService $stageChangeReasonService)
    {
        $relatedData = $stageChangeReasonService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
