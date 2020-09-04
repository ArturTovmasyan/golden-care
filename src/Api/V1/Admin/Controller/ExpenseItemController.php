<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ExpenseItemService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ExpenseItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/expense-item")
 *
 * @Grant(grant="persistence-common-expense_item", level="VIEW")
 *
 * Class ExpenseItemController
 * @package App\Api\V1\Admin\Controller
 */
class ExpenseItemController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_expense_item_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ExpenseItemService $expenseItemService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ExpenseItemService $expenseItemService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ExpenseItem::class,
            'api_admin_expense_item_grid',
            $expenseItemService
        );
    }

    /**
     * @Route("/grid", name="api_admin_expense_item_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ExpenseItem::class, 'api_admin_expense_item_grid');
    }

    /**
     * @Route("", name="api_admin_expense_item_list", methods={"GET"})
     *
     * @param Request $request
     * @param ExpenseItemService $expenseItemService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ExpenseItemService $expenseItemService)
    {
        return $this->respondList(
            $request,
            ExpenseItem::class,
            'api_admin_expense_item_list',
            $expenseItemService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_expense_item_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ExpenseItemService $expenseItemService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ExpenseItemService $expenseItemService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $expenseItemService->getById($id),
            ['api_admin_expense_item_get']
        );
    }

    /**
     * @Route("", name="api_admin_expense_item_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-expense_item", level="ADD")
     *
     * @param Request $request
     * @param ExpenseItemService $expenseItemService
     * @return JsonResponse
     */
    public function addAction(Request $request, ExpenseItemService $expenseItemService): JsonResponse
    {
        $id = $expenseItemService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_expense_item_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-expense_item", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ExpenseItemService $expenseItemService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ExpenseItemService $expenseItemService): JsonResponse
    {
        $expenseItemService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_expense_item_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-expense_item", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ExpenseItemService $expenseItemService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ExpenseItemService $expenseItemService): JsonResponse
    {
        $expenseItemService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_expense_item_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-expense_item", level="DELETE")
     *
     * @param Request $request
     * @param ExpenseItemService $expenseItemService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ExpenseItemService $expenseItemService): JsonResponse
    {
        $expenseItemService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_expense_item_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ExpenseItemService $expenseItemService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ExpenseItemService $expenseItemService): JsonResponse
    {
        $relatedData = $expenseItemService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
