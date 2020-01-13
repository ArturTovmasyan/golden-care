<?php

namespace App\Api\V1\Lead\Controller;

use App\Annotation\Grant;
use App\Api\V1\Lead\Service\ActivityService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\Activity;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/lead/activity")
 *
 * @Grant(grant="persistence-lead-activity", level="VIEW")
 *
 * Class ActivityController
 * @package App\Api\V1\Lead\Controller
 */
class ActivityController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_activity", methods={"GET"})
     *
     * @param Request $request
     * @param ActivityService $activityService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ActivityService $activityService): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->respondGrid(
            $request,
            Activity::class,
            'api_lead_activity_grid',
            $activityService,
            [
                'owner_type' => $request->get('owner_type'),
                'owner_id' => $request->get('owner_id'),
                'my' => $request->get('my'),
                'user_id' => $user->getId()
            ]
        );
    }

    /**
     * @Route("/grid", name="api_lead_activity_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Activity::class, 'api_lead_activity_grid');
    }

    /**
     * @Route("", name="api_lead_activity_list", methods={"GET"})
     *
     * @param Request $request
     * @param ActivityService $activityService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ActivityService $activityService)
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->respondList(
            $request,
            Activity::class,
            'api_lead_activity_list',
            $activityService,
            [
                'owner_type' => $request->get('owner_type'),
                'owner_id' => $request->get('owner_id'),
                'my' => $request->get('my'),
                'user_id' => $user->getId()
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_activity_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ActivityService $activityService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ActivityService $activityService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $activityService->getById($id),
            ['api_lead_activity_get']
        );
    }

    /**
     * @Route("", name="api_lead_activity_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-activity", level="ADD")
     *
     * @param Request $request
     * @param ActivityService $activityService
     * @return JsonResponse
     */
    public function addAction(Request $request, ActivityService $activityService): JsonResponse
    {
        $id = $activityService->add(
            [
                'type_id' => $request->get('type_id'),
                'owner_type' => $request->get('owner_type'),
                'title' => $request->get('title'),
                'date' => $request->get('date'),
                'notes' => $request->get('notes'),
                'status_id' => $request->get('status_id'),
                'assign_to_id' => $request->get('assign_to_id'),
                'due_date' => $request->get('due_date'),
                'reminder_date' => $request->get('reminder_date'),
                'facility_id' => $request->get('facility_id'),
                'task_contact_id' => $request->get('task_contact_id'),
                'amount' => $request->get('amount'),
                'lead_id' => $request->get('lead_id'),
                'referral_id' => $request->get('referral_id'),
                'organization_id' => $request->get('organization_id'),
                'outreach_id' => $request->get('outreach_id'),
                'contact_id' => $request->get('contact_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_activity_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-activity", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ActivityService $activityService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ActivityService $activityService): JsonResponse
    {
        $activityService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'date' => $request->get('date'),
                'notes' => $request->get('notes'),
                'status_id' => $request->get('status_id'),
                'assign_to_id' => $request->get('assign_to_id'),
                'due_date' => $request->get('due_date'),
                'reminder_date' => $request->get('reminder_date'),
                'facility_id' => $request->get('facility_id'),
                'task_contact_id' => $request->get('task_contact_id'),
                'amount' => $request->get('amount'),
                'lead_id' => $request->get('lead_id'),
                'referral_id' => $request->get('referral_id'),
                'organization_id' => $request->get('organization_id'),
                'outreach_id' => $request->get('outreach_id'),
                'contact_id' => $request->get('contact_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_activity_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-activity", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ActivityService $activityService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ActivityService $activityService): JsonResponse
    {
        $activityService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_activity_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-activity", level="DELETE")
     *
     * @param Request $request
     * @param ActivityService $activityService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ActivityService $activityService): JsonResponse
    {
        $activityService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_activity_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ActivityService $activityService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ActivityService $activityService): JsonResponse
    {
        $relatedData = $activityService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
