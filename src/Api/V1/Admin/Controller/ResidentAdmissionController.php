<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentAdmissionService;
use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Common\Service\Exception\IncorrectResidentStateException;
use App\Entity\ResidentAdmission;
use App\Model\ResidentState;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/api/v1.0/admin/resident/admission")
 *
 * @Grant(grant="persistence-resident-admission", level="VIEW")
 *
 * Class ResidentAdmissionController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentAdmissionController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_admission_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentAdmission::class,
            'api_admin_resident_admission_grid',
            $residentAdmissionService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_admission_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentAdmission::class, 'api_admin_resident_admission_grid');
    }

    /**
     * @Route("", name="api_admin_resident_admission_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentAdmissionService $residentAdmissionService)
    {
        return $this->respondList(
            $request,
            ResidentAdmission::class,
            'api_admin_resident_admission_list',
            $residentAdmissionService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_admission_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getById($id),
            ['api_admin_resident_admission_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_admission_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-admission", level="ADD")
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        $id = $residentAdmissionService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'group_type' => $request->get('group_type'),
                'admission_type' => $request->get('admission_type'),
                'date' => $request->get('date'),
                'facility_bed_id' => $request->get('facility_bed_id'),
                'apartment_bed_id' => $request->get('apartment_bed_id'),
                'region_id' => $request->get('region_id'),
                'csz_id' => $request->get('csz_id'),
                'address' => $request->get('address'),
                'dining_room_id' => $request->get('dining_room_id'),
                'dnr' => $request->get('dnr'),
                'polst' => $request->get('polst'),
                'ambulatory' => $request->get('ambulatory'),
                'care_group' => $request->get('care_group'),
                'care_level_id' => $request->get('care_level_id'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_admission_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-admission", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        $residentAdmissionService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'date' => $request->get('date'),
                'facility_bed_id' => $request->get('facility_bed_id'),
                'apartment_bed_id' => $request->get('apartment_bed_id'),
                'region_id' => $request->get('region_id'),
                'csz_id' => $request->get('csz_id'),
                'address' => $request->get('address'),
                'dining_room_id' => $request->get('dining_room_id'),
                'dnr' => $request->get('dnr'),
                'polst' => $request->get('polst'),
                'ambulatory' => $request->get('ambulatory'),
                'care_group' => $request->get('care_group'),
                'care_level_id' => $request->get('care_level_id'),
                'notes' => $request->get('notes') ?? ''
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_admission_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-admission", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        $residentAdmissionService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_admission_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-admission", level="DELETE")
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        $residentAdmissionService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_admission_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        $relatedData = $residentAdmissionService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }

    /**
     * @Route("/{id}/move", requirements={"id"="\d+"}, name="api_admin_resident_admission_move", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-admission", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function moveAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        $residentAdmissionService->move(
            $id,
            [
                'group_type' => $request->get('group_type'),
                'move_id' => $request->get('move_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/swap", name="api_admin_resident_admission_swap", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-admission", level="EDIT")
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function swapAction(Request $request, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        $residentAdmissionService->swap(
            [
                'first_id' => $request->get('first_id'),
                'second_id' => $request->get('second_id'),
                'date' => $request->get('date'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/beds", name="api_admin_resident_admission_by_bed_ids", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function getResidentsByBedIdsAction(Request $request, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        $residents = $residentAdmissionService->getResidentsByBedIds($request->get('type'), $request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$residents]
        );
    }

    /**
     * @Route("/{id}/active", requirements={"id"="\d+"}, name="api_admin_resident_admission_get_active_resident", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function getActiveResidentAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getActiveByResidentId($id),
            ['api_admin_resident_admission_get_active']
        );
    }

    /**
     * @Route("/{id}/active/base-rate", requirements={"id"="\d+"}, name="api_admin_resident_admission_get_active_resident_with_base_rate", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function getActiveResidentWithFacilityRoomBaseRateAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getActiveWithFacilityRoomBaseRateByResidentId($id),
            ['api_admin_resident_admission_get_active_with_base_rate']
        );
    }

    /**
     * @Route("/active/first", name="api_admin_resident_admission_get_active_first_residents", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function getActiveFirstResidentsAction(Request $request, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getActiveResidents(),
            ['api_admin_resident_get_active']
        );
    }

    /**
     * @Route("/{state}/{type}/{typeId}", requirements={"type"="\d+", "typeId"="\d+"}, name="api_admin_resident_admission_get_state_residents", methods={"GET"})
     *
     * @param Request $request
     * @param $state
     * @param $type
     * @param $typeId
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function getStateResidentsAction(Request $request, $state, $type, $typeId, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        switch ($state) {
            case ResidentState::TYPE_ACTIVE:
                $data = $residentAdmissionService->getActiveResidentsByStrategy($type, $typeId);
                break;
            case ResidentState::TYPE_INACTIVE:
                $data = $residentAdmissionService->getInactiveResidentsByStrategy($type, $typeId);
                break;
            case ResidentState::TYPE_NO_ADMISSION:
                $data = $residentAdmissionService->getNoAdmissionResidents();
                break;
            default:
                throw new IncorrectResidentStateException();
        }

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $data,
            ['api_admin_resident_get_active']
        );
    }

    /**
     * @Route("/paged/{state}/{page}/{perPage}", requirements={"page"="\d+", "perPage"="\d+"}, name="api_admin_resident_admission_get_pagination_residents", methods={"GET"})
     *
     * @param Request $request
     * @param $state
     * @param $page
     * @param $perPage
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function getPerPageResidentsAction(Request $request, $state, $page, $perPage, ResidentAdmissionService $residentAdmissionService): JsonResponse
    {
        $type = !empty($request->get('type')) ? (int)$request->get('type') : null;
        $typeId = !empty($request->get('type_id')) ? (int)$request->get('type_id') : null;

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getPerPageResidents($state, $page, $perPage, $type, $typeId),
            ['api_admin_resident_get_pagination']
        );
    }

    /**
     * @Route("/mobile/paged/{state}/{page}/{perPage}", requirements={"page"="\d+", "perPage"="\d+"}, name="api_admin_resident_admission_get_mobile_pagination_residents", methods={"GET"})
     *
     * @param Request $request
     * @param $state
     * @param $page
     * @param $perPage
     * @param ResidentAdmissionService $residentAdmissionService
     * @param RouterInterface $router
     * @return JsonResponse
     */
    public function getMobilePerPageResidentsAction(Request $request, $state, $page, $perPage, ResidentAdmissionService $residentAdmissionService, RouterInterface $router): JsonResponse
    {
        $type = !empty($request->get('type')) ? (int)$request->get('type') : null;
        $typeId = !empty($request->get('type_id')) ? (int)$request->get('type_id') : null;

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getMobilePerPageResidents($router, $state, $page, $perPage, $request->headers->get('date'), $type, $typeId),
            ['api_admin_resident_get_mobile_pagination']
        );
    }
}
