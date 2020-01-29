<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\FacilityRoomService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\FacilityRoom;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/facility/room")
 *
 * @Grant(grant="persistence-facility_room", level="VIEW")
 *
 * Class FacilityRoomController
 * @package App\Api\V1\Admin\Controller
 */
class FacilityRoomController extends BaseController
{
    protected function gridIgnoreFields(Request $request): array
    {
        $ignoreFields = [];

        $facilityId = (int)$request->get('facility_id');

        if (!empty($facilityId)) {
            $ignoreFields[] = 'facility';
            $ignoreFields[] = 'shorthand';
        }

        return $ignoreFields;
    }

    /**
     * @Route("/grid", name="api_admin_facility_room_grid", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     */
    public function gridAction(Request $request, FacilityRoomService $facilityRoomService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            FacilityRoom::class,
            'api_admin_facility_room_grid',
            $facilityRoomService,
            ['facility_id' => $request->get('facility_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_facility_room_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, FacilityRoom::class, 'api_admin_facility_room_grid');
    }

    /**
     * @Route("", name="api_admin_facility_room_list", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityRoomService $facilityRoomService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, FacilityRoomService $facilityRoomService)
    {
        return $this->respondList(
            $request,
            FacilityRoom::class,
            'api_admin_facility_room_list',
            $facilityRoomService,
            [
                'facility_id' => $request->get('facility_id'),
                'vacant' => $request->get('vacant')
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_room_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, FacilityRoomService $facilityRoomService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $facilityRoomService->getById($id),
            ['api_admin_facility_room_get']
        );
    }

    /**
     * @Route("", name="api_admin_facility_room_add", methods={"POST"})
     *
     * @Grant(grant="persistence-facility_room", level="ADD")
     *
     * @param Request $request
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     */
    public function addAction(Request $request, FacilityRoomService $facilityRoomService): JsonResponse
    {
        $id = $facilityRoomService->add(
            [
                'facility_id' => $request->get('facility_id'),
                'type_id' => $request->get('type_id'),
                'number' => $request->get('number'),
                'floor' => $request->get('floor'),
                'notes' => $request->get('notes') ?? '',
                'beds' => $request->get('beds')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_room_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-facility_room", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, FacilityRoomService $facilityRoomService): JsonResponse
    {
        $facilityRoomService->edit(
            $id,
            [
                'facility_id' => $request->get('facility_id'),
                'type_id' => $request->get('type_id'),
                'number' => $request->get('number'),
                'floor' => $request->get('floor'),
                'notes' => $request->get('notes') ?? '',
                'beds' => $request->get('beds')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_room_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_room", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, FacilityRoomService $facilityRoomService): JsonResponse
    {
        $facilityRoomService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_facility_room_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_room", level="DELETE")
     *
     * @param Request $request
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, FacilityRoomService $facilityRoomService): JsonResponse
    {
        $facilityRoomService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/{facility_id}/last", requirements={"facility_id"="\d+"}, name="api_admin_facility_room_get_last", methods={"GET"})
     *
     * @Grant(grant="persistence-facility_room", level="VIEW")
     *
     * @param Request $request
     * @param $facility_id
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     */
    public function getLastAction(Request $request, $facility_id, FacilityRoomService $facilityRoomService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$facilityRoomService->getLastNumber($facility_id)],
            ['api_admin_apartment_room_get_last']
        );
    }

    /**
     * @Route("/related/info", name="api_admin_facility_room_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param FacilityRoomService $facilityRoomService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, FacilityRoomService $facilityRoomService): JsonResponse
    {
        $relatedData = $facilityRoomService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
