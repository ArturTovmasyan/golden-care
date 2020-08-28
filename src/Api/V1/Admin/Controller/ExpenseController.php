<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ExpenseService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Expense;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/expense")
 *
 * @Grant(grant="persistence-common-expense", level="VIEW")
 *
 * Class ExpenseController
 * @package App\Api\V1\Admin\Controller
 */
class ExpenseController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_expense_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ExpenseService $expenseService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ExpenseService $expenseService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            Expense::class,
            'api_admin_expense_grid',
            $expenseService
        );
    }

    /**
     * @Route("/grid", name="api_admin_expense_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, Expense::class, 'api_admin_expense_grid');
    }

    /**
     * @Route("", name="api_admin_expense_list", methods={"GET"})
     *
     * @param Request $request
     * @param ExpenseService $expenseService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ExpenseService $expenseService)
    {
        return $this->respondList(
            $request,
            Expense::class,
            'api_admin_expense_list',
            $expenseService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_expense_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ExpenseService $expenseService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ExpenseService $expenseService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $expenseService->getById($id),
            ['api_admin_expense_get']
        );
    }

    /**
     * @Route("", name="api_admin_expense_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-expense", level="ADD")
     *
     * @param Request $request
     * @param ExpenseService $expenseService
     * @return JsonResponse
     */
    public function addAction(Request $request, ExpenseService $expenseService): JsonResponse
    {
        $id = $expenseService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_expense_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-expense", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ExpenseService $expenseService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ExpenseService $expenseService): JsonResponse
    {
        $expenseService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_expense_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-expense", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ExpenseService $expenseService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ExpenseService $expenseService): JsonResponse
    {
        $expenseService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_expense_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-expense", level="DELETE")
     *
     * @param Request $request
     * @param ExpenseService $expenseService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ExpenseService $expenseService): JsonResponse
    {
        $expenseService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_expense_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ExpenseService $expenseService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ExpenseService $expenseService): JsonResponse
    {
        $relatedData = $expenseService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
