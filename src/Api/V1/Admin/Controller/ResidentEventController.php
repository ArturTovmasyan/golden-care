<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentEventService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentEvent;
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
 * @Route("/api/v1.0/admin/resident/event")
 *
 * @Grant(grant="persistence-resident-resident_event", level="VIEW")
 *
 * Class ResidentEventController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentEventController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_event_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentEventService $residentEventService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentEventService $residentEventService)
    {
        return $this->respondGrid(
            $request,
            ResidentEvent::class,
            'api_admin_resident_event_grid',
            $residentEventService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_event_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName($request, ResidentEvent::class, 'api_admin_resident_event_grid');
    }

    /**
     * @Route("", name="api_admin_resident_event_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentEventService $residentEventService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function listAction(Request $request, ResidentEventService $residentEventService)
    {
        return $this->respondList(
            $request,
            ResidentEvent::class,
            'api_admin_resident_event_list',
            $residentEventService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_event_get", methods={"GET"})
     *
     * @param ResidentEventService $residentEventService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentEventService $residentEventService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentEventService->getById($id),
            ['api_admin_resident_event_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_event_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_event", level="ADD")
     *
     * @param Request $request
     * @param ResidentEventService $residentEventService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function addAction(Request $request, ResidentEventService $residentEventService)
    {
        $id = $residentEventService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'definition_id' => $request->get('definition_id'),
                'physician_id' => $request->get('physician_id'),
                'responsible_persons' => $request->get('responsible_persons'),
                'additional_date' => $request->get('additional_date'),
                'date' => $request->get('date'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_event_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_event", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentEventService $residentEventService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResidentEventService $residentEventService)
    {
        $residentEventService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'definition_id' => $request->get('definition_id'),
                'physician_id' => $request->get('physician_id'),
                'responsible_persons' => $request->get('responsible_persons'),
                'additional_date' => $request->get('additional_date'),
                'date' => $request->get('date'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_event_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_event", level="DELETE")
     *
     * @param $id
     * @param ResidentEventService $residentEventService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentEventService $residentEventService)
    {
        $residentEventService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_event_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_event", level="DELETE")
     *
     * @param Request $request
     * @param ResidentEventService $residentEventService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentEventService $residentEventService)
    {
        $residentEventService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_event_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentEventService $residentEventService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentEventService $residentEventService)
    {
        $relatedData = $residentEventService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
