<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ResidentAdmissionService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentAdmission;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use App\Annotation\Grant as Grant;

/**
 * @Route("/api/v1.0/admin/resident/admission")
 *
 * @Grant(grant="persistence-resident-admission", level="VIEW")
 *
 * Class AdmissionController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentAdmissionController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_admission_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    public function gridAction(Request $request, ResidentAdmissionService $residentAdmissionService)
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
     * @throws \ReflectionException
     */
    public function gridOptionAction(Request $request)
    {
        return $this->getOptionsByGroupName(ResidentAdmission::class, 'api_admin_resident_admission_grid');
    }

    /**
     * @Route("", name="api_admin_resident_admission_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse|PdfResponse
     * @throws \Throwable
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
     * @param ResidentAdmissionService $residentAdmissionService
     * @param $id
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService)
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
     * @throws \Throwable
     */
    public function addAction(Request $request, ResidentAdmissionService $residentAdmissionService)
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
     * @throws \Throwable
     */
    public function editAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService)
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
     * @param $id
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     * @throws \Throwable
     */
    public function deleteAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService)
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
     * @throws \Throwable
     */
    public function deleteBulkAction(Request $request, ResidentAdmissionService $residentAdmissionService)
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
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ResidentAdmissionService $residentAdmissionService)
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
     * @throws \Throwable
     */
    public function moveAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService)
    {
        $residentAdmissionService->move(
            $id,
            [
                'group_type'    => $request->get('group_type'),
                'move_id' => $request->get('move_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/{id}/active", requirements={"id"="\d+"}, name="api_admin_resident_admission_get_active_resident", methods={"GET"})
     *
     * @param ResidentAdmissionService $residentAdmissionService
     * @param $id
     * @return JsonResponse
     */
    public function getActiveResidentAction(Request $request, $id, ResidentAdmissionService $residentAdmissionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getActiveByResidentId($id),
            ['api_admin_resident_admission_get_active']
        );
    }

    /**
     * @Route("/active/first", name="api_admin_resident_admission_get_active_first_residents", methods={"GET"})
     *
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function getActiveFirstResidentsAction(Request $request, ResidentAdmissionService $residentAdmissionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getActiveResidents(),
            ['api_admin_resident_get_active']
        );
    }

    /**
     * @Route("/active/{type}/{typeId}", requirements={"type"="\d+", "typeId"="\d+"}, name="api_admin_resident_admission_get_active_residents", methods={"GET"})
     *
     * @param ResidentAdmissionService $residentAdmissionService
     * @param $type
     * @param $typeId
     * @return JsonResponse
     */
    public function getActiveResidentsAction(Request $request, $type, $typeId, ResidentAdmissionService $residentAdmissionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getActiveResidentsByStrategy($type, $typeId),
            ['api_admin_resident_get_active']
        );
    }

    /**
     * @Route("/paged/{state}/{page}/{perPage}", requirements={"page"="\d+", "perPage"="\d+"}, name="api_admin_resident_admission_get_pagination_residents", methods={"GET"})
     *
     * @param $state
     * @param $page
     * @param $perPage
     * @param $type
     * @param $typeId
     * @param ResidentAdmissionService $residentAdmissionService
     * @return JsonResponse
     */
    public function getPerPageResidentsAction(Request $request, $state, $page, $perPage, ResidentAdmissionService $residentAdmissionService)
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAdmissionService->getPerPageResidents($state, $page, $perPage, $request->get('type'), $request->get('type_id')),
            ['api_admin_resident_get_pagination']
        );
    }
}
