<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\FunnelStageService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\FunnelStage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/funnel-stage")
 *
 * @Grant(grant="persistence-lead-funnel_stage", level="VIEW")
 *
 * Class FunnelStageController
 * @package App\Api\V1\Lead\Controller
 */
class FunnelStageController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_funnel_stage", methods={"GET"})
     *
     * @param Request $request
     * @param FunnelStageService $funnelStageService
     * @return JsonResponse
     */
    public function gridAction(Request $request, FunnelStageService $funnelStageService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            FunnelStage::class,
            'api_lead_funnel_stage_grid',
            $funnelStageService
        );
    }

    /**
     * @Route("/grid", name="api_lead_funnel_stage_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, FunnelStage::class, 'api_lead_funnel_stage_grid');
    }

    /**
     * @Route("", name="api_lead_funnel_stage_list", methods={"GET"})
     *
     * @param Request $request
     * @param FunnelStageService $funnelStageService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, FunnelStageService $funnelStageService)
    {
        return $this->respondList(
            $request,
            FunnelStage::class,
            'api_lead_funnel_stage_list',
            $funnelStageService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_funnel_stage_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param FunnelStageService $funnelStageService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, FunnelStageService $funnelStageService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $funnelStageService->getById($id),
            ['api_lead_funnel_stage_get']
        );
    }

    /**
     * @Route("", name="api_lead_funnel_stage_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-funnel_stage", level="ADD")
     *
     * @param Request $request
     * @param FunnelStageService $funnelStageService
     * @return JsonResponse
     */
    public function addAction(Request $request, FunnelStageService $funnelStageService): JsonResponse
    {
        $id = $funnelStageService->add(
            [
                'title' => $request->get('title'),
                'seq_no' => $request->get('seq_no'),
                'open' => $request->get('open'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_funnel_stage_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-funnel_stage", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param FunnelStageService $funnelStageService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, FunnelStageService $funnelStageService): JsonResponse
    {
        $funnelStageService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'seq_no' => $request->get('seq_no'),
                'open' => $request->get('open'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_funnel_stage_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-funnel_stage", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param FunnelStageService $funnelStageService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, FunnelStageService $funnelStageService): JsonResponse
    {
        $funnelStageService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_funnel_stage_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-funnel_stage", level="DELETE")
     *
     * @param Request $request
     * @param FunnelStageService $funnelStageService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, FunnelStageService $funnelStageService): JsonResponse
    {
        $funnelStageService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_funnel_stage_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param FunnelStageService $funnelStageService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, FunnelStageService $funnelStageService): JsonResponse
    {
        $relatedData = $funnelStageService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
