<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\ActivityStatusService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\ActivityStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/activity-status")
 *
 * @Grant(grant="persistence-lead-activity_status", level="VIEW")
 *
 * Class ActivityStatusController
 * @package App\Api\V1\Lead\Controller
 */
class ActivityStatusController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_activity_status", methods={"GET"})
     *
     * @param Request $request
     * @param ActivityStatusService $activityStatusService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ActivityStatusService $activityStatusService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ActivityStatus::class,
            'api_lead_activity_status_grid',
            $activityStatusService
        );
    }

    /**
     * @Route("/grid", name="api_lead_activity_status_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ActivityStatus::class, 'api_lead_activity_status_grid');
    }

    /**
     * @Route("", name="api_lead_activity_status_list", methods={"GET"})
     *
     * @param Request $request
     * @param ActivityStatusService $activityStatusService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ActivityStatusService $activityStatusService)
    {
        return $this->respondList(
            $request,
            ActivityStatus::class,
            'api_lead_activity_status_list',
            $activityStatusService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_activity_status_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ActivityStatusService $activityStatusService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ActivityStatusService $activityStatusService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $activityStatusService->getById($id),
            ['api_lead_activity_status_get']
        );
    }

    /**
     * @Route("", name="api_lead_activity_status_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-activity_status", level="ADD")
     *
     * @param Request $request
     * @param ActivityStatusService $activityStatusService
     * @return JsonResponse
     */
    public function addAction(Request $request, ActivityStatusService $activityStatusService): JsonResponse
    {
        $id = $activityStatusService->add(
            [
                'title' => $request->get('title'),
                'done' => $request->get('done'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_activity_status_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-activity_status", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ActivityStatusService $activityStatusService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ActivityStatusService $activityStatusService): JsonResponse
    {
        $activityStatusService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'done' => $request->get('done'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_activity_status_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-activity_status", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ActivityStatusService $activityStatusService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ActivityStatusService $activityStatusService): JsonResponse
    {
        $activityStatusService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_activity_status_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-activity_status", level="DELETE")
     *
     * @param Request $request
     * @param ActivityStatusService $activityStatusService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ActivityStatusService $activityStatusService): JsonResponse
    {
        $activityStatusService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_activity_status_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ActivityStatusService $activityStatusService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ActivityStatusService $activityStatusService): JsonResponse
    {
        $relatedData = $activityStatusService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
