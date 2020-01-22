<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\FacilityRoomTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\FacilityRoomType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/facility-room-type")
 *
 * @Grant(grant="persistence-facility_room_type", level="VIEW")
 *
 * Class FacilityRoomTypeController
 * @package App\Api\V1\Admin\Controller
 */
class FacilityRoomTypeController extends BaseController
{
    protected function gridIgnoreFields(Request $request): array
    {
        $ignoreFields = [];

        $facilityId = (int)$request->get('facility_id');

        if (!empty($facilityId)) {
            $ignoreFields[] = 'facility';
        }

        return $ignoreFields;
    }

    /**
     * @Route("/grid", name="api_admin_facility_room_type_grid", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityRoomTypeService $facilityRoomTypeService
     * @return JsonResponse
     */
    public function gridAction(Request $request, FacilityRoomTypeService $facilityRoomTypeService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            FacilityRoomType::class,
            'api_admin_facility_room_type_grid',
            $facilityRoomTypeService,
            ['facility_id' => $request->get('facility_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_facility_room_type_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, FacilityRoomType::class, 'api_admin_facility_room_type_grid');
    }

    /**
     * @Route("", name="api_admin_facility_room_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityRoomTypeService $facilityRoomTypeService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, FacilityRoomTypeService $facilityRoomTypeService)
    {
        return $this->respondList(
            $request,
            FacilityRoomType::class,
            'api_admin_facility_room_type_list',
            $facilityRoomTypeService,
            ['facility_id' => $request->get('facility_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_room_type_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param FacilityRoomTypeService $facilityRoomTypeService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, FacilityRoomTypeService $facilityRoomTypeService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $facilityRoomTypeService->getById($id),
            ['api_admin_facility_room_type_get']
        );
    }

    /**
     * @Route("", name="api_admin_facility_room_type_add", methods={"POST"})
     *
     * @Grant(grant="persistence-facility_room_type", level="ADD")
     *
     * @param Request $request
     * @param FacilityRoomTypeService $facilityRoomTypeService
     * @return JsonResponse
     */
    public function addAction(Request $request, FacilityRoomTypeService $facilityRoomTypeService): JsonResponse
    {
        $id = $facilityRoomTypeService->add(
            [
                'facility_id' => $request->get('facility_id'),
                'title' => $request->get('title'),
                'private' => $request->get('private'),
                'description' => $request->get('description'),
                'base_rates' => $request->get('base_rates')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_room_type_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-facility_room_type", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param FacilityRoomTypeService $facilityRoomTypeService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, FacilityRoomTypeService $facilityRoomTypeService): JsonResponse
    {
        $facilityRoomTypeService->edit(
            $id,
            [
                'facility_id' => $request->get('facility_id'),
                'title' => $request->get('title'),
                'private' => $request->get('private'),
                'description' => $request->get('description'),
                'base_rates' => $request->get('base_rates')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_facility_room_type_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_room_type", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param FacilityRoomTypeService $facilityRoomTypeService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, FacilityRoomTypeService $facilityRoomTypeService): JsonResponse
    {
        $facilityRoomTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_facility_room_type_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-facility_room_type", level="DELETE")
     *
     * @param Request $request
     * @param FacilityRoomTypeService $facilityRoomTypeService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, FacilityRoomTypeService $facilityRoomTypeService): JsonResponse
    {
        $facilityRoomTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_facility_room_type_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param FacilityRoomTypeService $facilityRoomTypeService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, FacilityRoomTypeService $facilityRoomTypeService): JsonResponse
    {
        $relatedData = $facilityRoomTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
