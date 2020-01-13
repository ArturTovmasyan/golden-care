<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\OutreachService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\Outreach;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/outreach")
 *
 * @Grant(grant="persistence-lead-outreach", level="VIEW")
 *
 * Class OutreachController
 * @package App\Api\V1\Lead\Controller
 */
class OutreachController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_outreach", methods={"GET"})
     *
     * @param Request $request
     * @param OutreachService $outreachService
     * @return JsonResponse
     */
    public function gridAction(Request $request, OutreachService $outreachService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Outreach::class,
            'api_lead_outreach_grid',
            $outreachService
        );
    }

    /**
     * @Route("/grid", name="api_lead_outreach_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Outreach::class, 'api_lead_outreach_grid');
    }

    /**
     * @Route("", name="api_lead_outreach_list", methods={"GET"})
     *
     * @param Request $request
     * @param OutreachService $outreachService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, OutreachService $outreachService)
    {
        return $this->respondList(
            $request,
            Outreach::class,
            'api_lead_outreach_list',
            $outreachService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_outreach_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param OutreachService $outreachService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, OutreachService $outreachService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $outreachService->getById($id),
            ['api_lead_outreach_get']
        );
    }

    /**
     * @Route("", name="api_lead_outreach_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-outreach", level="ADD")
     *
     * @param Request $request
     * @param OutreachService $outreachService
     * @return JsonResponse
     */
    public function addAction(Request $request, OutreachService $outreachService): JsonResponse
    {
        $id = $outreachService->add(
            [
                'type_id' => $request->get('type_id'),
                'organization_id' => $request->get('organization_id'),
                'contacts' => $request->get('contacts'),
                'date' => $request->get('date'),
                'participants' => $request->get('participants'),
                'notes' => $request->get('notes')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_outreach_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-outreach", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param OutreachService $outreachService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, OutreachService $outreachService): JsonResponse
    {
        $outreachService->edit(
            $id,
            [
                'type_id' => $request->get('type_id'),
                'organization_id' => $request->get('organization_id'),
                'contacts' => $request->get('contacts'),
                'date' => $request->get('date'),
                'participants' => $request->get('participants'),
                'notes' => $request->get('notes')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_outreach_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-outreach", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param OutreachService $outreachService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, OutreachService $outreachService): JsonResponse
    {
        $outreachService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_outreach_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-outreach", level="DELETE")
     *
     * @param Request $request
     * @param OutreachService $outreachService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, OutreachService $outreachService): JsonResponse
    {
        $outreachService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_outreach_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param OutreachService $outreachService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, OutreachService $outreachService): JsonResponse
    {
        $relatedData = $outreachService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
