<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentCreditItemService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentCreditItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident-credit-item")
 *
 * @Grant(grant="persistence-resident-resident_credit_item", level="VIEW")
 *
 * Class ResidentCreditItemController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentCreditItemController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_credit_item_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentCreditItemService $residentCreditItemService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentCreditItemService $residentCreditItemService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentCreditItem::class,
            'api_admin_resident_credit_item_grid',
            $residentCreditItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_credit_item_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentCreditItem::class, 'api_admin_resident_credit_item_grid');
    }

    /**
     * @Route("", name="api_admin_resident_credit_item_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentCreditItemService $residentCreditItemService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentCreditItemService $residentCreditItemService)
    {
        return $this->respondList(
            $request,
            ResidentCreditItem::class,
            'api_admin_resident_credit_item_list',
            $residentCreditItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_credit_item_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentCreditItemService $residentCreditItemService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentCreditItemService $residentCreditItemService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentCreditItemService->getById($id),
            ['api_admin_resident_credit_item_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_credit_item_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_credit_item", level="ADD")
     *
     * @param Request $request
     * @param ResidentCreditItemService $residentCreditItemService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentCreditItemService $residentCreditItemService): JsonResponse
    {
        $id = $residentCreditItemService->add(
            [
                'ledger_id' => $request->get('ledger_id'),
                'credit_item_id' => $request->get('credit_item_id'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_credit_item_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_credit_item", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentCreditItemService $residentCreditItemService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentCreditItemService $residentCreditItemService): JsonResponse
    {
        $residentCreditItemService->edit(
            $id,
            [
                'ledger_id' => $request->get('ledger_id'),
                'credit_item_id' => $request->get('credit_item_id'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_credit_item_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_credit_item", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentCreditItemService $residentCreditItemService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentCreditItemService $residentCreditItemService): JsonResponse
    {
        $residentCreditItemService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_credit_item_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_credit_item", level="DELETE")
     *
     * @param Request $request
     * @param ResidentCreditItemService $residentCreditItemService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentCreditItemService $residentCreditItemService): JsonResponse
    {
        $residentCreditItemService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_credit_item_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentCreditItemService $residentCreditItemService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentCreditItemService $residentCreditItemService): JsonResponse
    {
        $relatedData = $residentCreditItemService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
