<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\CurrentResidenceService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\CurrentResidence;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/current-residence")
 *
 * @Grant(grant="persistence-lead-current_residence", level="VIEW")
 *
 * Class CurrentResidenceController
 * @package App\Api\V1\Lead\Controller
 */
class CurrentResidenceController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_current_residence", methods={"GET"})
     *
     * @param Request $request
     * @param CurrentResidenceService $currentResidenceService
     * @return JsonResponse
     */
    public function gridAction(Request $request, CurrentResidenceService $currentResidenceService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            CurrentResidence::class,
            'api_lead_current_residence_grid',
            $currentResidenceService
        );
    }

    /**
     * @Route("/grid", name="api_lead_current_residence_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, CurrentResidence::class, 'api_lead_current_residence_grid');
    }

    /**
     * @Route("", name="api_lead_current_residence_list", methods={"GET"})
     *
     * @param Request $request
     * @param CurrentResidenceService $currentResidenceService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, CurrentResidenceService $currentResidenceService)
    {
        return $this->respondList(
            $request,
            CurrentResidence::class,
            'api_lead_current_residence_list',
            $currentResidenceService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_current_residence_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param CurrentResidenceService $currentResidenceService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, CurrentResidenceService $currentResidenceService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $currentResidenceService->getById($id),
            ['api_lead_current_residence_get']
        );
    }

    /**
     * @Route("", name="api_lead_current_residence_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-current_residence", level="ADD")
     *
     * @param Request $request
     * @param CurrentResidenceService $currentResidenceService
     * @return JsonResponse
     */
    public function addAction(Request $request, CurrentResidenceService $currentResidenceService): JsonResponse
    {
        $id = $currentResidenceService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_current_residence_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-current_residence", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param CurrentResidenceService $currentResidenceService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, CurrentResidenceService $currentResidenceService): JsonResponse
    {
        $currentResidenceService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_current_residence_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-current_residence", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param CurrentResidenceService $currentResidenceService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, CurrentResidenceService $currentResidenceService): JsonResponse
    {
        $currentResidenceService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_current_residence_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-current_residence", level="DELETE")
     *
     * @param Request $request
     * @param CurrentResidenceService $currentResidenceService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, CurrentResidenceService $currentResidenceService): JsonResponse
    {
        $currentResidenceService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_current_residence_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param CurrentResidenceService $currentResidenceService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, CurrentResidenceService $currentResidenceService): JsonResponse
    {
        $relatedData = $currentResidenceService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
