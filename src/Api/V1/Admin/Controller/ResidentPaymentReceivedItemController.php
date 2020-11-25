<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentPaymentReceivedItemService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentPaymentReceivedItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident-payment-received-item")
 *
 * @Grant(grant="persistence-resident-resident_payment_received_item", level="VIEW")
 *
 * Class ResidentPaymentReceivedItemController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentPaymentReceivedItemController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_payment_received_item_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentPaymentReceivedItemService $residentPaymentReceivedItemService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentPaymentReceivedItemService $residentPaymentReceivedItemService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentPaymentReceivedItem::class,
            'api_admin_resident_payment_received_item_grid',
            $residentPaymentReceivedItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_payment_received_item_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentPaymentReceivedItem::class, 'api_admin_resident_payment_received_item_grid');
    }

    /**
     * @Route("", name="api_admin_resident_payment_received_item_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentPaymentReceivedItemService $residentPaymentReceivedItemService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentPaymentReceivedItemService $residentPaymentReceivedItemService)
    {
        return $this->respondList(
            $request,
            ResidentPaymentReceivedItem::class,
            'api_admin_resident_payment_received_item_list',
            $residentPaymentReceivedItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_payment_received_item_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentPaymentReceivedItemService $residentPaymentReceivedItemService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentPaymentReceivedItemService $residentPaymentReceivedItemService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentPaymentReceivedItemService->getById($id),
            ['api_admin_resident_payment_received_item_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_payment_received_item_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_payment_received_item", level="ADD")
     *
     * @param Request $request
     * @param ResidentPaymentReceivedItemService $residentPaymentReceivedItemService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentPaymentReceivedItemService $residentPaymentReceivedItemService): JsonResponse
    {
        $id = $residentPaymentReceivedItemService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_payment_received_item_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_payment_received_item", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentPaymentReceivedItemService $residentPaymentReceivedItemService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentPaymentReceivedItemService $residentPaymentReceivedItemService): JsonResponse
    {
        $residentPaymentReceivedItemService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_payment_received_item_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_payment_received_item", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentPaymentReceivedItemService $residentPaymentReceivedItemService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentPaymentReceivedItemService $residentPaymentReceivedItemService): JsonResponse
    {
        $residentPaymentReceivedItemService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_payment_received_item_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_payment_received_item", level="DELETE")
     *
     * @param Request $request
     * @param ResidentPaymentReceivedItemService $residentPaymentReceivedItemService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentPaymentReceivedItemService $residentPaymentReceivedItemService): JsonResponse
    {
        $residentPaymentReceivedItemService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_payment_received_item_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentPaymentReceivedItemService $residentPaymentReceivedItemService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentPaymentReceivedItemService $residentPaymentReceivedItemService): JsonResponse
    {
        $relatedData = $residentPaymentReceivedItemService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
