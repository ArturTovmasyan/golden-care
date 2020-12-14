<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentExpenseItemService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentExpenseItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident-expense-item")
 *
 * @Grant(grant="persistence-resident-resident_expense_item", level="VIEW")
 *
 * Class ResidentExpenseItemController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentExpenseItemController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_expense_item_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentExpenseItemService $residentExpenseItemService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentExpenseItemService $residentExpenseItemService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentExpenseItem::class,
            'api_admin_resident_expense_item_grid',
            $residentExpenseItemService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_expense_item_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentExpenseItem::class, 'api_admin_resident_expense_item_grid');
    }

    /**
     * @Route("", name="api_admin_resident_expense_item_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentExpenseItemService $residentExpenseItemService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentExpenseItemService $residentExpenseItemService)
    {
        return $this->respondList(
            $request,
            ResidentExpenseItem::class,
            'api_admin_resident_expense_item_list',
            $residentExpenseItemService,
            ['resident_id' => $request->get('resident_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_expense_item_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentExpenseItemService $residentExpenseItemService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentExpenseItemService $residentExpenseItemService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentExpenseItemService->getById($id),
            ['api_admin_resident_expense_item_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_expense_item_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_expense_item", level="ADD")
     *
     * @param Request $request
     * @param ResidentExpenseItemService $residentExpenseItemService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentExpenseItemService $residentExpenseItemService): JsonResponse
    {
        $id = $residentExpenseItemService->add(
            [
                'resident_id' => $request->get('resident_id'),
                'expense_item_id' => $request->get('expense_item_id'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_expense_item_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_expense_item", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentExpenseItemService $residentExpenseItemService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentExpenseItemService $residentExpenseItemService): JsonResponse
    {
        $residentExpenseItemService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id'),
                'expense_item_id' => $request->get('expense_item_id'),
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_expense_item_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_expense_item", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentExpenseItemService $residentExpenseItemService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentExpenseItemService $residentExpenseItemService): JsonResponse
    {
        $residentExpenseItemService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_expense_item_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_expense_item", level="DELETE")
     *
     * @param Request $request
     * @param ResidentExpenseItemService $residentExpenseItemService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentExpenseItemService $residentExpenseItemService): JsonResponse
    {
        $residentExpenseItemService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_expense_item_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentExpenseItemService $residentExpenseItemService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentExpenseItemService $residentExpenseItemService): JsonResponse
    {
        $relatedData = $residentExpenseItemService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
