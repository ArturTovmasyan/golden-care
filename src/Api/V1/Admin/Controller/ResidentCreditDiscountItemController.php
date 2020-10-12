<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentCreditDiscountItemService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentCreditDiscountItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident-credit-discount-item")
 *
 * @Grant(grant="persistence-resident-resident_credit_discount_item", level="VIEW")
 *
 * Class ResidentCreditDiscountItemController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentCreditDiscountItemController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_credit_discount_item_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentCreditDiscountItemService $residentCreditDiscountItemService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentCreditDiscountItemService $residentCreditDiscountItemService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentCreditDiscountItem::class,
            'api_admin_resident_credit_discount_item_grid',
            $residentCreditDiscountItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_credit_discount_item_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentCreditDiscountItem::class, 'api_admin_resident_credit_discount_item_grid');
    }

    /**
     * @Route("", name="api_admin_resident_credit_discount_item_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentCreditDiscountItemService $residentCreditDiscountItemService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentCreditDiscountItemService $residentCreditDiscountItemService)
    {
        return $this->respondList(
            $request,
            ResidentCreditDiscountItem::class,
            'api_admin_resident_credit_discount_item_list',
            $residentCreditDiscountItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_credit_discount_item_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentCreditDiscountItemService $residentCreditDiscountItemService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentCreditDiscountItemService $residentCreditDiscountItemService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentCreditDiscountItemService->getById($id),
            ['api_admin_resident_credit_discount_item_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_credit_discount_item_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_credit_discount_item", level="ADD")
     *
     * @param Request $request
     * @param ResidentCreditDiscountItemService $residentCreditDiscountItemService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentCreditDiscountItemService $residentCreditDiscountItemService): JsonResponse
    {
        $id = $residentCreditDiscountItemService->add(
            [
                'ledger_id' => $request->get('ledger_id'),
                'credit_discount_item_id' => $request->get('credit_discount_item_id'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_credit_discount_item_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_credit_discount_item", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentCreditDiscountItemService $residentCreditDiscountItemService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentCreditDiscountItemService $residentCreditDiscountItemService): JsonResponse
    {
        $residentCreditDiscountItemService->edit(
            $id,
            [
                'ledger_id' => $request->get('ledger_id'),
                'credit_discount_item_id' => $request->get('credit_discount_item_id'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_credit_discount_item_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_credit_discount_item", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentCreditDiscountItemService $residentCreditDiscountItemService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentCreditDiscountItemService $residentCreditDiscountItemService): JsonResponse
    {
        $residentCreditDiscountItemService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_credit_discount_item_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_credit_discount_item", level="DELETE")
     *
     * @param Request $request
     * @param ResidentCreditDiscountItemService $residentCreditDiscountItemService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentCreditDiscountItemService $residentCreditDiscountItemService): JsonResponse
    {
        $residentCreditDiscountItemService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_credit_discount_item_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentCreditDiscountItemService $residentCreditDiscountItemService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentCreditDiscountItemService $residentCreditDiscountItemService): JsonResponse
    {
        $relatedData = $residentCreditDiscountItemService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
