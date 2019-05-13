<?php
namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Lead\Service\ActivityStatusService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\ActivityStatus;
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
 * @Route("/api/v1.0/lead/activity/status")
 *
 * @Grant(grant="persistence-lead-activity_status", level="VIEW")
 *
 * Class ActivityStatusController
 * @package App\Api\V1\Admin\Controller
 */
class ActivityStatusController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_activity_status", methods={"GET"})
     *
     * @param Request $request
     * @param ActivityStatusService $activityStatusService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ActivityStatusService $activityStatusService)
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
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ActivityStatus::class, 'api_lead_activity_status_grid');
    }

    /**
     * @Route("", name="api_lead_activity_status_list", methods={"GET"})
     *
     * @param Request $request
     * @param ActivityStatusService $activityStatusService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
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
     * @param ActivityStatusService $activityStatusService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ActivityStatusService $activityStatusService)
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
     * @throws \Exception
     */
    public function addAction(Request $request, ActivityStatusService $activityStatusService)
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
     * @throws \Exception
     */
    public function editAction(Request $request, $id, ActivityStatusService $activityStatusService)
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
     * @param $id
     * @param ActivityStatusService $activityStatusService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ActivityStatusService $activityStatusService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ActivityStatusService $activityStatusService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ActivityStatusService $activityStatusService)
    {
        $relatedData = $activityStatusService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
