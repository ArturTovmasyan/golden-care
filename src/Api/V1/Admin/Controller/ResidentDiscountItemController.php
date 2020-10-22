<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentDiscountItemService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentDiscountItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident-discount-item")
 *
 * @Grant(grant="persistence-resident-resident_discount_item", level="VIEW")
 *
 * Class ResidentDiscountItemController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentDiscountItemController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_discount_item_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentDiscountItemService $residentDiscountItemService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentDiscountItemService $residentDiscountItemService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentDiscountItem::class,
            'api_admin_resident_discount_item_grid',
            $residentDiscountItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_discount_item_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentDiscountItem::class, 'api_admin_resident_discount_item_grid');
    }

    /**
     * @Route("", name="api_admin_resident_discount_item_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentDiscountItemService $residentDiscountItemService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentDiscountItemService $residentDiscountItemService)
    {
        return $this->respondList(
            $request,
            ResidentDiscountItem::class,
            'api_admin_resident_discount_item_list',
            $residentDiscountItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_discount_item_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentDiscountItemService $residentDiscountItemService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentDiscountItemService $residentDiscountItemService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentDiscountItemService->getById($id),
            ['api_admin_resident_discount_item_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_discount_item_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_discount_item", level="ADD")
     *
     * @param Request $request
     * @param ResidentDiscountItemService $residentDiscountItemService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentDiscountItemService $residentDiscountItemService): JsonResponse
    {
        $id = $residentDiscountItemService->add(
            [
                'ledger_id' => $request->get('ledger_id'),
                'discount_item_id' => $request->get('discount_item_id'),
                'date' => $request->get('date'),
                'amount' => $request->get('amount'),
                'notes' => $request->get('notes') ?? '',
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_discount_item_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_discount_item", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentDiscountItemService $residentDiscountItemService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentDiscountItemService $residentDiscountItemService): JsonResponse
    {
        $residentDiscountItemService->edit(
            $id,
            [
                'ledger_id' => $request->get('ledger_id'),
                'discount_item_id' => $request->get('discount_item_id'),
                'date' => $request->get('date'),
                'amount' => $request->get('amount'),
                'notes' => $request->get('notes') ?? '',
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_discount_item_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_discount_item", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentDiscountItemService $residentDiscountItemService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentDiscountItemService $residentDiscountItemService): JsonResponse
    {
        $residentDiscountItemService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_discount_item_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_discount_item", level="DELETE")
     *
     * @param Request $request
     * @param ResidentDiscountItemService $residentDiscountItemService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentDiscountItemService $residentDiscountItemService): JsonResponse
    {
        $residentDiscountItemService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_discount_item_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentDiscountItemService $residentDiscountItemService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentDiscountItemService $residentDiscountItemService): JsonResponse
    {
        $relatedData = $residentDiscountItemService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
