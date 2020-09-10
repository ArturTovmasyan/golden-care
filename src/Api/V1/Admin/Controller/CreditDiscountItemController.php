<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\CreditDiscountItemService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\CreditDiscountItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/credit-discount-item")
 *
 * @Grant(grant="persistence-common-credit_discount_item", level="VIEW")
 *
 * Class CreditDiscountItemController
 * @package App\Api\V1\Admin\Controller
 */
class CreditDiscountItemController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_credit_discount_item_grid", methods={"GET"})
     *
     * @param Request $request
     * @param CreditDiscountItemService $creditDiscountItemService
     * @return JsonResponse
     */
    public function gridAction(Request $request, CreditDiscountItemService $creditDiscountItemService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            CreditDiscountItem::class,
            'api_admin_credit_discount_item_grid',
            $creditDiscountItemService
        );
    }

    /**
     * @Route("/grid", name="api_admin_credit_discount_item_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, CreditDiscountItem::class, 'api_admin_credit_discount_item_grid');
    }

    /**
     * @Route("", name="api_admin_credit_discount_item_list", methods={"GET"})
     *
     * @param Request $request
     * @param CreditDiscountItemService $creditDiscountItemService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, CreditDiscountItemService $creditDiscountItemService)
    {
        return $this->respondList(
            $request,
            CreditDiscountItem::class,
            'api_admin_credit_discount_item_list',
            $creditDiscountItemService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_credit_discount_item_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param CreditDiscountItemService $creditDiscountItemService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, CreditDiscountItemService $creditDiscountItemService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $creditDiscountItemService->getById($id),
            ['api_admin_credit_discount_item_get']
        );
    }

    /**
     * @Route("", name="api_admin_credit_discount_item_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-credit_discount_item", level="ADD")
     *
     * @param Request $request
     * @param CreditDiscountItemService $creditDiscountItemService
     * @return JsonResponse
     */
    public function addAction(Request $request, CreditDiscountItemService $creditDiscountItemService): JsonResponse
    {
        $id = $creditDiscountItemService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_credit_discount_item_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-credit_discount_item", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param CreditDiscountItemService $creditDiscountItemService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, CreditDiscountItemService $creditDiscountItemService): JsonResponse
    {
        $creditDiscountItemService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_credit_discount_item_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-credit_discount_item", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param CreditDiscountItemService $creditDiscountItemService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, CreditDiscountItemService $creditDiscountItemService): JsonResponse
    {
        $creditDiscountItemService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_credit_discount_item_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-credit_discount_item", level="DELETE")
     *
     * @param Request $request
     * @param CreditDiscountItemService $creditDiscountItemService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, CreditDiscountItemService $creditDiscountItemService): JsonResponse
    {
        $creditDiscountItemService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_credit_discount_item_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param CreditDiscountItemService $creditDiscountItemService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, CreditDiscountItemService $creditDiscountItemService): JsonResponse
    {
        $relatedData = $creditDiscountItemService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
