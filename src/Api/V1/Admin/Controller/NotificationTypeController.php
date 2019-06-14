<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\NotificationTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\NotificationType;
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
 * @Route("/api/v1.0/admin/notification-type")
 *
 * @Grant(grant="persistence-common-notification_type", level="VIEW")
 *
 * Class NotificationTypeController
 * @package  App\Api\V1\Admin\Controller
 */
class NotificationTypeController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_notification_type", methods={"GET"})
     *
     * @param Request $request
     * @param NotificationTypeService $activityTypeService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, NotificationTypeService $activityTypeService)
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
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(NotificationType::class, 'api_admin_notification_type_grid');
    }

    /**
     * @Route("", name="api_admin_notification_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param NotificationTypeService $activityTypeService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
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
     * @param NotificationTypeService $activityTypeService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, NotificationTypeService $activityTypeService)
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
     * @throws \Exception
     */
    public function addAction(Request $request, NotificationTypeService $activityTypeService)
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
     * @throws \Exception
     */
    public function editAction(Request $request, $id, NotificationTypeService $activityTypeService)
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
     * @param $id
     * @param NotificationTypeService $activityTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, NotificationTypeService $activityTypeService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, NotificationTypeService $activityTypeService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, NotificationTypeService $activityTypeService)
    {
        $relatedData = $activityTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
