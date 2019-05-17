<?php
namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Lead\Service\ActivityService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\Activity;
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
 * @Route("/api/v1.0/lead/activity")
 *
 * @Grant(grant="persistence-lead-activity", level="VIEW")
 *
 * Class ActivityController
 * @package App\Api\V1\Admin\Controller
 */
class ActivityController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_activity", methods={"GET"})
     *
     * @param Request $request
     * @param ActivityService $activityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ActivityService $activityService)
    {
        return $this->respondGrid(
            $request,
            Activity::class,
            'api_lead_activity_grid',
            $activityService
        );
    }

    /**
     * @Route("/grid", name="api_lead_activity_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Activity::class, 'api_lead_activity_grid');
    }

    /**
     * @Route("", name="api_lead_activity_list", methods={"GET"})
     *
     * @param Request $request
     * @param ActivityService $activityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ActivityService $activityService)
    {
        return $this->respondList(
            $request,
            Activity::class,
            'api_lead_activity_list',
            $activityService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_activity_get", methods={"GET"})
     *
     * @param ActivityService $activityService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ActivityService $activityService)
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
     * @throws \Exception
     */
    public function addAction(Request $request, ActivityService $activityService)
    {
        $id = $activityService->add(
            [
                'type_id' => $request->get('type_id'),
                'owner_type' => $request->get('owner_type'),
                'title' => $request->get('title'),
                'date' => $request->get('date'),
                'notes' => $request->get('notes'),
                'status_id' => $request->get('status_id'),
                'assign_to' => $request->get('assign_to'),
                'due_date' => $request->get('due_date'),
                'reminder_date' => $request->get('reminder_date'),
                'facility_id' => $request->get('facility_id'),
                'referral_id' => $request->get('referral_id'),
                'organization_id' => $request->get('organization_id')
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
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ActivityService $activityService)
    {
        $activityService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'date' => $request->get('date'),
                'notes' => $request->get('notes'),
                'status_id' => $request->get('status_id'),
                'assign_to' => $request->get('assign_to'),
                'due_date' => $request->get('due_date'),
                'reminder_date' => $request->get('reminder_date'),
                'facility_id' => $request->get('facility_id'),
                'referral_id' => $request->get('referral_id'),
                'organization_id' => $request->get('organization_id')
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
     * @param $id
     * @param ActivityService $activityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ActivityService $activityService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ActivityService $activityService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ActivityService $activityService)
    {
        $relatedData = $activityService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
