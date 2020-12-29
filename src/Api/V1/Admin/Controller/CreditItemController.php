<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\CreditItemService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\CreditItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/credit-item")
 *
 * @Grant(grant="persistence-common-credit_item", level="VIEW")
 *
 * Class CreditItemController
 * @package App\Api\V1\Admin\Controller
 */
class CreditItemController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_credit_item_grid", methods={"GET"})
     *
     * @param Request $request
     * @param CreditItemService $creditItemService
     * @return JsonResponse
     */
    public function gridAction(Request $request, CreditItemService $creditItemService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            CreditItem::class,
            'api_admin_credit_item_grid',
            $creditItemService
        );
    }

    /**
     * @Route("/grid", name="api_admin_credit_item_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, CreditItem::class, 'api_admin_credit_item_grid');
    }

    /**
     * @Route("", name="api_admin_credit_item_list", methods={"GET"})
     *
     * @param Request $request
     * @param CreditItemService $creditItemService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, CreditItemService $creditItemService)
    {
        return $this->respondList(
            $request,
            CreditItem::class,
            'api_admin_credit_item_list',
            $creditItemService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_credit_item_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param CreditItemService $creditItemService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, CreditItemService $creditItemService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $creditItemService->getById($id),
            ['api_admin_credit_item_get']
        );
    }

    /**
     * @Route("", name="api_admin_credit_item_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-credit_item", level="ADD")
     *
     * @param Request $request
     * @param CreditItemService $creditItemService
     * @return JsonResponse
     */
    public function addAction(Request $request, CreditItemService $creditItemService): JsonResponse
    {
        $id = $creditItemService->add(
            [
                'title' => $request->get('title'),
                'amount' => $request->get('amount'),
                'can_be_changed' => $request->get('can_be_changed'),
                'valid_through_date' => $request->get('valid_through_date'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_credit_item_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-credit_item", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param CreditItemService $creditItemService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, CreditItemService $creditItemService): JsonResponse
    {
        $creditItemService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'amount' => $request->get('amount'),
                'can_be_changed' => $request->get('can_be_changed'),
                'valid_through_date' => $request->get('valid_through_date'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_credit_item_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-credit_item", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param CreditItemService $creditItemService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, CreditItemService $creditItemService): JsonResponse
    {
        $creditItemService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_credit_item_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-credit_item", level="DELETE")
     *
     * @param Request $request
     * @param CreditItemService $creditItemService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, CreditItemService $creditItemService): JsonResponse
    {
        $creditItemService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_credit_item_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param CreditItemService $creditItemService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, CreditItemService $creditItemService): JsonResponse
    {
        $relatedData = $creditItemService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
