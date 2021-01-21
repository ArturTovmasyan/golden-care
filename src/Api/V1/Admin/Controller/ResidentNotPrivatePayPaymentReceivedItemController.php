<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentNotPrivatePayPaymentReceivedItemService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentNotPrivatePayPaymentReceivedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident-not-private-pay-payment-received-item")
 *
 * @Grant(grant="persistence-resident-resident_not_private_pay_payment_received_item", level="VIEW")
 *
 * Class ResidentNotPaymentReceivedItemController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentNotPrivatePayPaymentReceivedItemController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_not_private_pay_payment_received_item_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentNotPrivatePayPaymentReceivedItem::class,
            'api_admin_resident_not_private_pay_payment_received_item_grid',
            $residentNotPrivatePayPaymentReceivedItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_not_private_pay_payment_received_item_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentNotPrivatePayPaymentReceivedItem::class, 'api_admin_resident_not_private_pay_payment_received_item_grid');
    }

    /**
     * @Route("", name="api_admin_resident_not_private_pay_payment_received_item_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService)
    {
        return $this->respondList(
            $request,
            ResidentNotPrivatePayPaymentReceivedItem::class,
            'api_admin_resident_not_private_pay_payment_received_item_list',
            $residentNotPrivatePayPaymentReceivedItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_not_private_pay_payment_received_item_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentNotPrivatePayPaymentReceivedItemService->getById($id),
            ['api_admin_resident_not_private_pay_payment_received_item_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_not_private_pay_payment_received_item_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_not_private_pay_payment_received_item", level="ADD")
     *
     * @param Request $request
     * @param ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService): JsonResponse
    {
        $id = $residentNotPrivatePayPaymentReceivedItemService->add(
            [
                'ledger_id' => $request->get('ledger_id'),
                'payment_type_id' => $request->get('payment_type_id'),
                'date' => $request->get('date'),
                'amount' => $request->get('amount'),
                'transaction_number' => $request->get('transaction_number'),
                'notes' => $request->get('notes') ?? '',
                'rent_id' => $request->get('rent_id'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_not_private_pay_payment_received_item_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_not_private_pay_payment_received_item", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService): JsonResponse
    {
        $residentNotPrivatePayPaymentReceivedItemService->edit(
            $id,
            [
                'ledger_id' => $request->get('ledger_id'),
                'payment_type_id' => $request->get('payment_type_id'),
                'date' => $request->get('date'),
                'amount' => $request->get('amount'),
                'transaction_number' => $request->get('transaction_number'),
                'notes' => $request->get('notes') ?? '',
                'rent_id' => $request->get('rent_id'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_not_private_pay_payment_received_item_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_not_private_pay_payment_received_item", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService): JsonResponse
    {
        $residentNotPrivatePayPaymentReceivedItemService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_not_private_pay_payment_received_item_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_not_private_pay_payment_received_item", level="DELETE")
     *
     * @param Request $request
     * @param ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService): JsonResponse
    {
        $residentNotPrivatePayPaymentReceivedItemService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_not_private_pay_payment_received_item_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentNotPrivatePayPaymentReceivedItemService $residentNotPrivatePayPaymentReceivedItemService): JsonResponse
    {
        $relatedData = $residentNotPrivatePayPaymentReceivedItemService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
