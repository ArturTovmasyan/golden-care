<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\DiscountItemService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\DiscountItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/discount-item")
 *
 * @Grant(grant="persistence-common-discount_item", level="VIEW")
 *
 * Class DiscountItemController
 * @package App\Api\V1\Admin\Controller
 */
class DiscountItemController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_discount_item_grid", methods={"GET"})
     *
     * @param Request $request
     * @param DiscountItemService $discountItemService
     * @return JsonResponse
     */
    public function gridAction(Request $request, DiscountItemService $discountItemService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            DiscountItem::class,
            'api_admin_discount_item_grid',
            $discountItemService
        );
    }

    /**
     * @Route("/grid", name="api_admin_discount_item_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, DiscountItem::class, 'api_admin_discount_item_grid');
    }

    /**
     * @Route("", name="api_admin_discount_item_list", methods={"GET"})
     *
     * @param Request $request
     * @param DiscountItemService $discountItemService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, DiscountItemService $discountItemService)
    {
        return $this->respondList(
            $request,
            DiscountItem::class,
            'api_admin_discount_item_list',
            $discountItemService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_discount_item_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param DiscountItemService $discountItemService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, DiscountItemService $discountItemService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $discountItemService->getById($id),
            ['api_admin_discount_item_get']
        );
    }

    /**
     * @Route("", name="api_admin_discount_item_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-discount_item", level="ADD")
     *
     * @param Request $request
     * @param DiscountItemService $discountItemService
     * @return JsonResponse
     */
    public function addAction(Request $request, DiscountItemService $discountItemService): JsonResponse
    {
        $id = $discountItemService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_discount_item_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-discount_item", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param DiscountItemService $discountItemService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, DiscountItemService $discountItemService): JsonResponse
    {
        $discountItemService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_discount_item_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-discount_item", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param DiscountItemService $discountItemService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, DiscountItemService $discountItemService): JsonResponse
    {
        $discountItemService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_discount_item_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-discount_item", level="DELETE")
     *
     * @param Request $request
     * @param DiscountItemService $discountItemService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, DiscountItemService $discountItemService): JsonResponse
    {
        $discountItemService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_discount_item_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param DiscountItemService $discountItemService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, DiscountItemService $discountItemService): JsonResponse
    {
        $relatedData = $discountItemService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
