<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\FacilityRoomBaseRateService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\FacilityRoomBaseRate;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/v1.0/admin/facility-room-base-rate")
 *
 * @Grant(grant="persistence-facility_room_base_rate", level="VIEW")
 *
 * Class FacilityRoomBaseRateController
 * @package App\Api\V1\Admin\Controller
 */
class FacilityRoomBaseRateController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_facility_room_base_rate_grid", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityRoomBaseRateService $facilityRoomBaseRateService
     * @return JsonResponse
     */
    public function gridAction(Request $request, FacilityRoomBaseRateService $facilityRoomBaseRateService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            FacilityRoomBaseRate::class,
            'api_admin_facility_room_base_rate_grid',
            $facilityRoomBaseRateService,
            ['room_type_id' => $request->get('room_type_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_facility_room_base_rate_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, FacilityRoomBaseRate::class, 'api_admin_facility_room_base_rate_grid');
    }

    /**
     * @Route("", name="api_admin_facility_room_base_rate_list", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityRoomBaseRateService $facilityRoomBaseRateService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, FacilityRoomBaseRateService $facilityRoomBaseRateService)
    {
        return $this->respondList(
            $request,
            FacilityRoomBaseRate::class,
            'api_admin_facility_room_base_rate_list',
            $facilityRoomBaseRateService,
            ['room_type_id' => $request->get('room_type_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_room_base_rate_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param FacilityRoomBaseRateService $facilityRoomBaseRateService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, FacilityRoomBaseRateService $facilityRoomBaseRateService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $facilityRoomBaseRateService->getById($id),
            ['api_admin_facility_room_base_rate_get']
        );
    }

    /**
     * @Route("", name="api_admin_facility_room_base_rate_add", methods={"POST"})
     *
     * @Grant(grant="persistence-facility_room_base_rate", level="ADD")
     *
     * @param Request $request
     * @param FacilityRoomBaseRateService $facilityRoomBaseRateService
     * @return JsonResponse
     */
    public function addAction(Request $request, FacilityRoomBaseRateService $facilityRoomBaseRateService): JsonResponse
    {
        $id = $facilityRoomBaseRateService->add(
            [
                'room_type_id' => $request->get('room_type_id'),
                'date' => $request->get('date'),
                'levels' => $request->get('levels')

            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_room_base_rate_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-facility_room_base_rate", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param FacilityRoomBaseRateService $facilityRoomBaseRateService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, FacilityRoomBaseRateService $facilityRoomBaseRateService): JsonResponse
    {
        $facilityRoomBaseRateService->edit(
            $id,
            [
                'room_type_id' => $request->get('room_type_id'),
                'date' => $request->get('date'),
                'levels' => $request->get('levels')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_room_base_rate_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_room_base_rate", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param FacilityRoomBaseRateService $facilityRoomBaseRateService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, FacilityRoomBaseRateService $facilityRoomBaseRateService): JsonResponse
    {
        $facilityRoomBaseRateService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_facility_room_base_rate_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_room_base_rate", level="DELETE")
     *
     * @param Request $request
     * @param FacilityRoomBaseRateService $facilityRoomBaseRateService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, FacilityRoomBaseRateService $facilityRoomBaseRateService): JsonResponse
    {
        $facilityRoomBaseRateService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_facility_room_base_rate_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param FacilityRoomBaseRateService $facilityRoomBaseRateService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, FacilityRoomBaseRateService $facilityRoomBaseRateService): JsonResponse
    {
        $relatedData = $facilityRoomBaseRateService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
