<?php
namespace App\Api\V1\Lead\Controller;

use App\Api\V1\Lead\Service\ActivityTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Lead\ActivityType;
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
 * @Route("/api/v1.0/lead/activity-type")
 *
 * @Grant(grant="persistence-lead-activity_type", level="VIEW")
 *
 * Class ActivityTypeController
 * @package App\Api\V1\Admin\Controller
 */
class ActivityTypeController extends BaseController
{
    /**
     * @Route("/grid", name="api_lead_activity_type", methods={"GET"})
     *
     * @param Request $request
     * @param ActivityTypeService $activityTypeService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ActivityTypeService $activityTypeService)
    {
        return $this->respondGrid(
            $request,
            ActivityType::class,
            'api_lead_activity_type_grid',
            $activityTypeService
        );
    }

    /**
     * @Route("/grid", name="api_lead_activity_type_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, ActivityType::class, 'api_lead_activity_type_grid');
    }

    /**
     * @Route("", name="api_lead_activity_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param ActivityTypeService $activityTypeService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ActivityTypeService $activityTypeService)
    {
        return $this->respondList(
            $request,
            ActivityType::class,
            'api_lead_activity_type_list',
            $activityTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_activity_type_get", methods={"GET"})
     *
     * @param ActivityTypeService $activityTypeService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ActivityTypeService $activityTypeService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $activityTypeService->getById($id),
            ['api_lead_activity_type_get']
        );
    }

    /**
     * @Route("", name="api_lead_activity_type_add", methods={"POST"})
     *
     * @Grant(grant="persistence-lead-activity_type", level="ADD")
     *
     * @param Request $request
     * @param ActivityTypeService $activityTypeService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ActivityTypeService $activityTypeService)
    {
        $id = $activityTypeService->add(
            [
                'title' => $request->get('title'),
                'default_status_id' => $request->get('default_status_id'),
                'assign_to' => $request->get('assign_to'),
                'due_date' => $request->get('due_date'),
                'reminder_date' => $request->get('reminder_date'),
                'cc' => $request->get('cc'),
                'sms' => $request->get('sms'),
                'facility' => $request->get('facility'),
                'contact' => $request->get('contact'),
                'amount' => $request->get('amount'),
                'editable' => $request->get('editable'),
                'deletable' => $request->get('deletable')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_activity_type_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-lead-activity_type", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ActivityTypeService $activityTypeService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ActivityTypeService $activityTypeService)
    {
        $activityTypeService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'default_status_id' => $request->get('default_status_id'),
                'assign_to' => $request->get('assign_to'),
                'due_date' => $request->get('due_date'),
                'reminder_date' => $request->get('reminder_date'),
                'cc' => $request->get('cc'),
                'sms' => $request->get('sms'),
                'facility' => $request->get('facility'),
                'contact' => $request->get('contact'),
                'amount' => $request->get('amount'),
                'editable' => $request->get('editable'),
                'deletable' => $request->get('deletable')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_lead_activity_type_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-activity_type", level="DELETE")
     *
     * @param $id
     * @param ActivityTypeService $activityTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ActivityTypeService $activityTypeService)
    {
        $activityTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_lead_activity_type_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-lead-activity_type", level="DELETE")
     *
     * @param Request $request
     * @param ActivityTypeService $activityTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ActivityTypeService $activityTypeService)
    {
        $activityTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_lead_activity_type_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ActivityTypeService $activityTypeService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ActivityTypeService $activityTypeService)
    {
        $relatedData = $activityTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
