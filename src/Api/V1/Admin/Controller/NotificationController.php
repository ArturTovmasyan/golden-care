<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\NotificationService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Notification;
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
 * @Route("/api/v1.0/admin/notification")
 *
 * @Grant(grant="persistence-common-notification", level="VIEW")
 *
 * Class NotificationController
 * @package  App\Api\V1\Admin\Controller
 */
class NotificationController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_notification", methods={"GET"})
     *
     * @param Request $request
     * @param NotificationService $activityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, NotificationService $activityService)
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
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(Notification::class, 'api_admin_notification_grid');
    }

    /**
     * @Route("", name="api_admin_notification_list", methods={"GET"})
     *
     * @param Request $request
     * @param NotificationService $activityService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
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
     * @param NotificationService $activityService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, NotificationService $activityService)
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
     * @throws \Exception
     */
    public function addAction(Request $request, NotificationService $activityService)
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
     * @throws \Exception
     */
    public function editAction(Request $request, $id, NotificationService $activityService)
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
     * @param $id
     * @param NotificationService $activityService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, NotificationService $activityService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, NotificationService $activityService)
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
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, NotificationService $activityService)
    {
        $relatedData = $activityService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
