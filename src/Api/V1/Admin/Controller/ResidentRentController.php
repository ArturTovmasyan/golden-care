<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentRentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentRent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident/rent")
 *
 * @Grant(grant="persistence-resident-resident_rent", level="VIEW")
 *
 * Class ResidentRentController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentRentController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_rent_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentRentService $residentRentService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentRent::class,
            'api_admin_resident_rent_grid',
            $residentRentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_rent_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentRent::class, 'api_admin_resident_rent_grid');
    }

    /**
     * @Route("", name="api_admin_resident_rent_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentRentService $residentRentService)
    {
        return $this->respondList(
            $request,
            ResidentRent::class,
            'api_admin_resident_rent_list',
            $residentRentService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentRentService $residentRentService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentRentService->getById($id),
            ['api_admin_resident_rent_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_rent_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_rent", level="ADD")
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentRentService $residentRentService): JsonResponse
    {
        $id = $residentRentService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'start' => $request->get('start'),
                'end' => $request->get('end'),
                'period' => $request->get('period'),
                'amount' => $request->get('amount'),
                'notes' => $request->get('notes') ?? '',
                'source' => $request->get('source'),
                'reason_id' => $request->get('reason_id'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_rent", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentRentService $residentRentService): JsonResponse
    {
        $residentRentService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'start' => $request->get('start'),
                'end' => $request->get('end'),
                'period' => $request->get('period'),
                'amount' => $request->get('amount'),
                'notes' => $request->get('notes') ?? '',
                'source' => $request->get('source'),
                'reason_id' => $request->get('reason_id'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_rent_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_rent", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentRentService $residentRentService): JsonResponse
    {
        $residentRentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_rent_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_rent", level="DELETE")
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentRentService $residentRentService): JsonResponse
    {
        $residentRentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_rent_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentRentService $residentRentService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentRentService $residentRentService): JsonResponse
    {
        $relatedData = $residentRentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
