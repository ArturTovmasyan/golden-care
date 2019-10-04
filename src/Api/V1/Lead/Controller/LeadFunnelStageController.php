<?php
namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Lead\Service\LeadFunnelStageService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\LeadFunnelStage;
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
 * @Route("/api/v1.0/lead/lead-funnel-stage")
 *
 * @Grant(grant="persistence-lead-lead_funnel_stage", level="VIEW")
 *
 * Class LeadFunnelStageController
 * @package App\Api\V1\Admin\Controller
 */
class LeadFunnelStageController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_lead_funnel_stage", methods={"GET"})
     *
     * @param Request $request
     * @param LeadFunnelStageService $leadFunnelStageService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, LeadFunnelStageService $leadFunnelStageService)
    {
        return $this->respondGrid(
            $request,
            LeadFunnelStage::class,
            'api_lead_lead_funnel_stage_grid',
            $leadFunnelStageService,
            [
                'lead_id' => $request->get('lead_id'),
            ]
        );
    }

    /**
     * @Route("/grid", name="api_lead_lead_funnel_stage_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, LeadFunnelStage::class, 'api_lead_lead_funnel_stage_grid');
    }

    /**
     * @Route("", name="api_lead_lead_funnel_stage_list", methods={"GET"})
     *
     * @param Request $request
     * @param LeadFunnelStageService $leadFunnelStageService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, LeadFunnelStageService $leadFunnelStageService)
    {
        return $this->respondList(
            $request,
            LeadFunnelStage::class,
            'api_lead_lead_funnel_stage_list',
            $leadFunnelStageService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_lead_funnel_stage_get", methods={"GET"})
     *
     * @param LeadFunnelStageService $leadFunnelStageService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, LeadFunnelStageService $leadFunnelStageService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $leadFunnelStageService->getById($id),
            ['api_lead_lead_funnel_stage_get']
        );
    }

    /**
     * @Route("", name="api_lead_lead_funnel_stage_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-lead_funnel_stage", level="ADD")
     *
     * @param Request $request
     * @param LeadFunnelStageService $leadFunnelStageService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, LeadFunnelStageService $leadFunnelStageService)
    {
        $id = $leadFunnelStageService->add(
            [
                'lead_id' => $request->get('lead_id'),
                'stage_id' => $request->get('stage_id'),
                'reason_id' => $request->get('reason_id'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_lead_funnel_stage_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-lead_funnel_stage", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param LeadFunnelStageService $leadFunnelStageService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, LeadFunnelStageService $leadFunnelStageService)
    {
        $leadFunnelStageService->edit(
            $id,
            [
                'lead_id' => $request->get('lead_id'),
                'stage_id' => $request->get('stage_id'),
                'reason_id' => $request->get('reason_id'),
                'date' => $request->get('date'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_lead_funnel_stage_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-lead_funnel_stage", level="DELETE")
     *
     * @param $id
     * @param LeadFunnelStageService $leadFunnelStageService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, LeadFunnelStageService $leadFunnelStageService)
    {
        $leadFunnelStageService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_lead_funnel_stage_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-lead_funnel_stage", level="DELETE")
     *
     * @param Request $request
     * @param LeadFunnelStageService $leadFunnelStageService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, LeadFunnelStageService $leadFunnelStageService)
    {
        $leadFunnelStageService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_lead_funnel_stage_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param LeadFunnelStageService $leadFunnelStageService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, LeadFunnelStageService $leadFunnelStageService)
    {
        $relatedData = $leadFunnelStageService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
