<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\DiscountService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Discount;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/discount")
 *
 * @Grant(grant="persistence-common-discount", level="VIEW")
 *
 * Class DiscountController
 * @package App\Api\V1\Admin\Controller
 */
class DiscountController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_discount_grid", methods={"GET"})
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @return JsonResponse
     */
    public function gridAction(Request $request, DiscountService $discountService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Discount::class,
            'api_admin_discount_grid',
            $discountService
        );
    }

    /**
     * @Route("/grid", name="api_admin_discount_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Discount::class, 'api_admin_discount_grid');
    }

    /**
     * @Route("", name="api_admin_discount_list", methods={"GET"})
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, DiscountService $discountService)
    {
        return $this->respondList(
            $request,
            Discount::class,
            'api_admin_discount_list',
            $discountService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_discount_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param DiscountService $discountService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, DiscountService $discountService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $discountService->getById($id),
            ['api_admin_discount_get']
        );
    }

    /**
     * @Route("", name="api_admin_discount_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-discount", level="ADD")
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @return JsonResponse
     */
    public function addAction(Request $request, DiscountService $discountService): JsonResponse
    {
        $id = $discountService->add(
            [
                'title' => $request->get('title'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_discount_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-discount", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param DiscountService $discountService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, DiscountService $discountService): JsonResponse
    {
        $discountService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_discount_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-discount", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param DiscountService $discountService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, DiscountService $discountService): JsonResponse
    {
        $discountService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_discount_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-discount", level="DELETE")
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, DiscountService $discountService): JsonResponse
    {
        $discountService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_discount_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param DiscountService $discountService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, DiscountService $discountService): JsonResponse
    {
        $relatedData = $discountService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
