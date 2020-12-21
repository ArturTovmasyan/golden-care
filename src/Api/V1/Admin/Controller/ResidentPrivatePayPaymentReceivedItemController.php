<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentPrivatePayPaymentReceivedItemService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentPrivatePayPaymentReceivedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident-private-pay-payment-received-item")
 *
 * @Grant(grant="persistence-resident-resident_private_pay_payment_received_item", level="VIEW")
 *
 * Class ResidentPaymentReceivedItemController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentPrivatePayPaymentReceivedItemController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_private_pay_payment_received_item_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentPrivatePayPaymentReceivedItem::class,
            'api_admin_resident_private_pay_payment_received_item_grid',
            $residentPrivatePayPaymentReceivedItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_private_pay_payment_received_item_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentPrivatePayPaymentReceivedItem::class, 'api_admin_resident_private_pay_payment_received_item_grid');
    }

    /**
     * @Route("", name="api_admin_resident_private_pay_payment_received_item_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService)
    {
        return $this->respondList(
            $request,
            ResidentPrivatePayPaymentReceivedItem::class,
            'api_admin_resident_private_pay_payment_received_item_list',
            $residentPrivatePayPaymentReceivedItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_private_pay_payment_received_item_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentPrivatePayPaymentReceivedItemService->getById($id),
            ['api_admin_resident_private_pay_payment_received_item_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_private_pay_payment_received_item_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_private_pay_payment_received_item", level="ADD")
     *
     * @param Request $request
     * @param ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService): JsonResponse
    {
        $id = $residentPrivatePayPaymentReceivedItemService->add(
            [
                'ledger_id' => $request->get('ledger_id'),
                'payment_type_id' => $request->get('payment_type_id'),
                'date' => $request->get('date'),
                'amount' => $request->get('amount'),
                'transaction_number' => $request->get('transaction_number'),
                'notes' => $request->get('notes') ?? '',
                'responsible_person_id' => $request->get('responsible_person_id'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_private_pay_payment_received_item_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_private_pay_payment_received_item", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService): JsonResponse
    {
        $residentPrivatePayPaymentReceivedItemService->edit(
            $id,
            [
                'ledger_id' => $request->get('ledger_id'),
                'payment_type_id' => $request->get('payment_type_id'),
                'date' => $request->get('date'),
                'amount' => $request->get('amount'),
                'transaction_number' => $request->get('transaction_number'),
                'notes' => $request->get('notes') ?? '',
                'responsible_person_id' => $request->get('responsible_person_id'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_private_pay_payment_received_item_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_private_pay_payment_received_item", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService): JsonResponse
    {
        $residentPrivatePayPaymentReceivedItemService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_private_pay_payment_received_item_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_private_pay_payment_received_item", level="DELETE")
     *
     * @param Request $request
     * @param ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService): JsonResponse
    {
        $residentPrivatePayPaymentReceivedItemService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_private_pay_payment_received_item_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentPrivatePayPaymentReceivedItemService $residentPrivatePayPaymentReceivedItemService): JsonResponse
    {
        $relatedData = $residentPrivatePayPaymentReceivedItemService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
