<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\FacilityEventService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\FacilityEvent;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/facility/event")
 *
 * @Grant(grant="persistence-facility_event", level="VIEW")
 *
 * Class FacilityEventController
 * @package App\Api\V1\Admin\Controller
 */
class FacilityEventController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_facility_event_grid", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityEventService $facilityEventService
     * @return JsonResponse
     */
    public function gridAction(Request $request, FacilityEventService $facilityEventService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            FacilityEvent::class,
            'api_admin_facility_event_grid',
            $facilityEventService,
            ['facility_id' => $request->get('facility_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_facility_event_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, FacilityEvent::class, 'api_admin_facility_event_grid');
    }

    /**
     * @Route("", name="api_admin_facility_event_list", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityEventService $facilityEventService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, FacilityEventService $facilityEventService)
    {
        return $this->respondList(
            $request,
            FacilityEvent::class,
            'api_admin_facility_event_list',
            $facilityEventService,
            ['facility_id' => $request->get('facility_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_event_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param FacilityEventService $facilityEventService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, FacilityEventService $facilityEventService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $facilityEventService->getById($id),
            ['api_admin_facility_event_get']
        );
    }

    /**
     * @Route("", name="api_admin_facility_event_add", methods={"POST"})
     *
     * @Grant(grant="persistence-facility_event", level="ADD")
     *
     * @param Request $request
     * @param FacilityEventService $facilityEventService
     * @return JsonResponse
     */
    public function addAction(Request $request, FacilityEventService $facilityEventService): JsonResponse
    {
        $id = $facilityEventService->add(
            [
                'facility_id' => $request->get('facility_id'),
                'definition_id' => $request->get('definition_id'),
                'title' => $request->get('title'),
                'start_date' => $request->get('start_date'),
                'start_time' => $request->get('start_time'),
                'end_date' => $request->get('end_date'),
                'end_time' => $request->get('end_time'),
                'users' => $request->get('users'),
                'residents' => $request->get('residents'),
                'all_day' => $request->get('all_day'),
                'repeat' => $request->get('repeat'),
                'repeat_end' => $request->get('repeat_end'),
                'no_repeat_end' => $request->get('no_repeat_end'),
                'rsvp' => $request->get('rsvp'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_event_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-facility_event", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param FacilityEventService $facilityEventService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, FacilityEventService $facilityEventService): JsonResponse
    {
        $facilityEventService->edit(
            $id,
            [
                'facility_id' => $request->get('facility_id'),
                'definition_id' => $request->get('definition_id'),
                'title' => $request->get('title'),
                'start_date' => $request->get('start_date'),
                'start_time' => $request->get('start_time'),
                'end_date' => $request->get('end_date'),
                'end_time' => $request->get('end_time'),
                'users' => $request->get('users'),
                'residents' => $request->get('residents'),
                'all_day' => $request->get('all_day'),
                'repeat' => $request->get('repeat'),
                'repeat_end' => $request->get('repeat_end'),
                'no_repeat_end' => $request->get('no_repeat_end'),
                'rsvp' => $request->get('rsvp'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_event_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_event", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param FacilityEventService $facilityEventService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, FacilityEventService $facilityEventService): JsonResponse
    {
        $facilityEventService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_facility_event_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_event", level="DELETE")
     *
     * @param Request $request
     * @param FacilityEventService $facilityEventService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, FacilityEventService $facilityEventService): JsonResponse
    {
        $facilityEventService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_facility_event_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param FacilityEventService $facilityEventService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, FacilityEventService $facilityEventService): JsonResponse
    {
        $relatedData = $facilityEventService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/rsvp/{id}", requirements={"id"="\d+"}, name="api_admin_facility_event_get_is_rsvp", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param FacilityEventService $facilityEventService
     * @return JsonResponse
     */
    public function getIsDoneAction(Request $request, $id, FacilityEventService $facilityEventService): JsonResponse
    {
        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $facilityEventService->getIsRsvp($id, $user),
            ['api_admin_facility_event_get_is_rsvp']
        );
    }
}
