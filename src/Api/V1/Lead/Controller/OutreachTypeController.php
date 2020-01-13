<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\OutreachTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\OutreachType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/outreach-type")
 *
 * @Grant(grant="persistence-lead-outreach_type", level="VIEW")
 *
 * Class OutreachTypeController
 * @package App\Api\V1\Lead\Controller
 */
class OutreachTypeController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_outreach_type", methods={"GET"})
     *
     * @param Request $request
     * @param OutreachTypeService $outreachTypeService
     * @return JsonResponse
     */
    public function gridAction(Request $request, OutreachTypeService $outreachTypeService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            OutreachType::class,
            'api_lead_outreach_type_grid',
            $outreachTypeService
        );
    }

    /**
     * @Route("/grid", name="api_lead_outreach_type_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, OutreachType::class, 'api_lead_outreach_type_grid');
    }

    /**
     * @Route("", name="api_lead_outreach_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param OutreachTypeService $outreachTypeService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, OutreachTypeService $outreachTypeService)
    {
        return $this->respondList(
            $request,
            OutreachType::class,
            'api_lead_outreach_type_list',
            $outreachTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_outreach_type_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param OutreachTypeService $outreachTypeService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, OutreachTypeService $outreachTypeService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $outreachTypeService->getById($id),
            ['api_lead_outreach_type_get']
        );
    }

    /**
     * @Route("", name="api_lead_outreach_type_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-outreach_type", level="ADD")
     *
     * @param Request $request
     * @param OutreachTypeService $outreachTypeService
     * @return JsonResponse
     */
    public function addAction(Request $request, OutreachTypeService $outreachTypeService): JsonResponse
    {
        $id = $outreachTypeService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_outreach_type_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-outreach_type", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param OutreachTypeService $outreachTypeService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, OutreachTypeService $outreachTypeService): JsonResponse
    {
        $outreachTypeService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_outreach_type_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-outreach_type", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param OutreachTypeService $outreachTypeService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, OutreachTypeService $outreachTypeService): JsonResponse
    {
        $outreachTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_outreach_type_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-outreach_type", level="DELETE")
     *
     * @param Request $request
     * @param OutreachTypeService $outreachTypeService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, OutreachTypeService $outreachTypeService): JsonResponse
    {
        $outreachTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_outreach_type_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param OutreachTypeService $outreachTypeService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, OutreachTypeService $outreachTypeService): JsonResponse
    {
        $relatedData = $outreachTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
