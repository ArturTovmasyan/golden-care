<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\NotificationService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Notification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/notification")
 *
 * @Grant(grant="persistence-common-notification", level="VIEW")
 *
 * Class NotificationController
 * @package App\Api\V1\Admin\Controller
 */
class NotificationController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_notification", methods={"GET"})
     *
     * @param Request $request
     * @param NotificationService $activityService
     * @return JsonResponse
     */
    public function gridAction(Request $request, NotificationService $activityService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Notification::class,
            'api_admin_notification_grid',
            $activityService
        );
    }

    /**
     * @Route("/grid", name="api_admin_notification_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Notification::class, 'api_admin_notification_grid');
    }

    /**
     * @Route("", name="api_admin_notification_list", methods={"GET"})
     *
     * @param Request $request
     * @param NotificationService $activityService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, NotificationService $activityService)
    {
        return $this->respondList(
            $request,
            Notification::class,
            'api_admin_notification_list',
            $activityService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_notification_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param NotificationService $activityService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, NotificationService $activityService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $activityService->getById($id),
            ['api_admin_notification_get']
        );
    }

    /**
     * @Route("", name="api_admin_notification_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-notification", level="ADD")
     *
     * @param Request $request
     * @param NotificationService $activityService
     * @return JsonResponse
     */
    public function addAction(Request $request, NotificationService $activityService): JsonResponse
    {
        $id = $activityService->add(
            [
                'type_id' => $request->get('type_id'),
                'enabled' => $request->get('enabled'),
                'schedule' => $request->get('schedule'),
                'emails' => $request->get('emails'),
                'users' => $request->get('users'),
                'facilities' => $request->get('facilities'),
                'apartments' => $request->get('apartments'),
                'regions' => $request->get('regions')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_notification_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-notification", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param NotificationService $activityService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, NotificationService $activityService): JsonResponse
    {
        $activityService->edit(
            $id,
            [
                'type_id' => $request->get('type_id'),
                'enabled' => $request->get('enabled'),
                'schedule' => $request->get('schedule'),
                'emails' => $request->get('emails'),
                'users' => $request->get('users'),
                'facilities' => $request->get('facilities'),
                'apartments' => $request->get('apartments'),
                'regions' => $request->get('regions')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_notification_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-notification", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param NotificationService $activityService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, NotificationService $activityService): JsonResponse
    {
        $activityService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_notification_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-notification", level="DELETE")
     *
     * @param Request $request
     * @param NotificationService $activityService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, NotificationService $activityService): JsonResponse
    {
        $activityService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_notification_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param NotificationService $activityService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, NotificationService $activityService): JsonResponse
    {
        $relatedData = $activityService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
