<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentRentIncreaseService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentRentIncrease;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident-rent-increase")
 *
 * @Grant(grant="persistence-resident-resident_rent_increase", level="VIEW")
 *
 * Class ResidentRentIncreaseController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentRentIncreaseController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_rent_increase_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentRentIncreaseService $residentRentService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentRentIncrease::class,
            'api_admin_resident_rent_increase_grid',
            $residentRentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_rent_increase_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentRentIncrease::class, 'api_admin_resident_rent_increase_grid');
    }

    /**
     * @Route("", name="api_admin_resident_rent_increase_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentRentIncreaseService $residentRentService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentRentIncreaseService $residentRentService)
    {
        return $this->respondList(
            $request,
            ResidentRentIncrease::class,
            'api_admin_resident_rent_increase_list',
            $residentRentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_increase_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentRentIncreaseService $residentRentService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentRentService->getById($id),
            ['api_admin_resident_rent_increase_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_rent_increase_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_rent_increase", level="ADD")
     *
     * @param Request $request
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentRentIncreaseService $residentRentService): JsonResponse
    {
        $id = $residentRentService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'reason_id' => $request->get('reason_id'),
                'amount' => $request->get('amount'),
                'effective_date' => $request->get('effective_date'),
                'notification_date' => $request->get('notification_date'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_increase_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_rent_increase", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentRentIncreaseService $residentRentService): JsonResponse
    {
        $residentRentService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'reason_id' => $request->get('reason_id'),
                'amount' => $request->get('amount'),
                'effective_date' => $request->get('effective_date'),
                'notification_date' => $request->get('notification_date'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_increase_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_rent_increase", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentRentIncreaseService $residentRentService): JsonResponse
    {
        $residentRentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_rent_increase_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_rent_increase", level="DELETE")
     *
     * @param Request $request
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentRentIncreaseService $residentRentService): JsonResponse
    {
        $residentRentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_rent_increase_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentRentIncreaseService $residentRentService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentRentIncreaseService $residentRentService): JsonResponse
    {
        $relatedData = $residentRentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
