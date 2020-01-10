<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\NotificationTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\NotificationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/notification-type")
 *
 * @Grant(grant="persistence-common-notification_type", level="VIEW")
 *
 * Class NotificationTypeController
 * @package App\Api\V1\Admin\Controller
 */
class NotificationTypeController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_notification_type", methods={"GET"})
     *
     * @param Request $request
     * @param NotificationTypeService $activityTypeService
     * @return JsonResponse
     */
    public function gridAction(Request $request, NotificationTypeService $activityTypeService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            NotificationType::class,
            'api_admin_notification_type_grid',
            $activityTypeService
        );
    }

    /**
     * @Route("/grid", name="api_admin_notification_type_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, NotificationType::class, 'api_admin_notification_type_grid');
    }

    /**
     * @Route("", name="api_admin_notification_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param NotificationTypeService $activityTypeService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, NotificationTypeService $activityTypeService)
    {
        return $this->respondList(
            $request,
            NotificationType::class,
            'api_admin_notification_type_list',
            $activityTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_notification_type_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param NotificationTypeService $activityTypeService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, NotificationTypeService $activityTypeService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $activityTypeService->getById($id),
            ['api_admin_notification_type_get']
        );
    }

    /**
     * @Route("", name="api_admin_notification_type_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-notification_type", level="ADD")
     *
     * @param Request $request
     * @param NotificationTypeService $activityTypeService
     * @return JsonResponse
     */
    public function addAction(Request $request, NotificationTypeService $activityTypeService): JsonResponse
    {
        $id = $activityTypeService->add(
            [
                'category' => $request->get('category'),
                'title' => $request->get('title'),
                'email' => $request->get('email'),
                'sms' => $request->get('sms'),
                'facility' => $request->get('facility'),
                'apartment' => $request->get('apartment'),
                'region' => $request->get('region'),
                'email_subject' => $request->get('email_subject'),
                'email_message' => $request->get('email_message'),
                'sms_subject' => $request->get('sms_subject'),
                'sms_message' => $request->get('sms_message'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_notification_type_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-notification_type", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param NotificationTypeService $activityTypeService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, NotificationTypeService $activityTypeService): JsonResponse
    {
        $activityTypeService->edit(
            $id,
            [
                'category' => $request->get('category'),
                'title' => $request->get('title'),
                'email' => $request->get('email'),
                'sms' => $request->get('sms'),
                'facility' => $request->get('facility'),
                'apartment' => $request->get('apartment'),
                'region' => $request->get('region'),
                'email_subject' => $request->get('email_subject'),
                'email_message' => $request->get('email_message'),
                'sms_subject' => $request->get('sms_subject'),
                'sms_message' => $request->get('sms_message'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_notification_type_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-notification_type", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param NotificationTypeService $activityTypeService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, NotificationTypeService $activityTypeService): JsonResponse
    {
        $activityTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_notification_type_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-notification_type", level="DELETE")
     *
     * @param Request $request
     * @param NotificationTypeService $activityTypeService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, NotificationTypeService $activityTypeService): JsonResponse
    {
        $activityTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_notification_type_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param NotificationTypeService $activityTypeService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, NotificationTypeService $activityTypeService): JsonResponse
    {
        $relatedData = $activityTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
