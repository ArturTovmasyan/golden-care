<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentKeyFinanceDateService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentKeyFinanceDate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident-key-finance-date")
 *
 * @Grant(grant="persistence-resident-resident_key_finance_date", level="VIEW")
 *
 * Class ResidentKeyFinanceDateController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentKeyFinanceDateController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_key_finance_date_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentKeyFinanceDateService $residentKeyFinanceDateService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentKeyFinanceDateService $residentKeyFinanceDateService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentKeyFinanceDate::class,
            'api_admin_resident_key_finance_date_grid',
            $residentKeyFinanceDateService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_key_finance_date_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentKeyFinanceDate::class, 'api_admin_resident_key_finance_date_grid');
    }

    /**
     * @Route("", name="api_admin_resident_key_finance_date_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentKeyFinanceDateService $residentKeyFinanceDateService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentKeyFinanceDateService $residentKeyFinanceDateService)
    {
        return $this->respondList(
            $request,
            ResidentKeyFinanceDate::class,
            'api_admin_resident_key_finance_date_list',
            $residentKeyFinanceDateService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_key_finance_date_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentKeyFinanceDateService $residentKeyFinanceDateService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentKeyFinanceDateService $residentKeyFinanceDateService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentKeyFinanceDateService->getById($id),
            ['api_admin_resident_key_finance_date_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_key_finance_date_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_key_finance_date", level="ADD")
     *
     * @param Request $request
     * @param ResidentKeyFinanceDateService $residentKeyFinanceDateService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentKeyFinanceDateService $residentKeyFinanceDateService): JsonResponse
    {
        $id = $residentKeyFinanceDateService->add(
            [
                'ledger_id' => $request->get('ledger_id'),
                'key_finance_type_id' => $request->get('key_finance_type_id'),
                'date' => $request->get('date'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_key_finance_date_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_key_finance_date", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentKeyFinanceDateService $residentKeyFinanceDateService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentKeyFinanceDateService $residentKeyFinanceDateService): JsonResponse
    {
        $residentKeyFinanceDateService->edit(
            $id,
            [
                'ledger_id' => $request->get('ledger_id'),
                'date' => $request->get('date'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_key_finance_date_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_key_finance_date", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentKeyFinanceDateService $residentKeyFinanceDateService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentKeyFinanceDateService $residentKeyFinanceDateService): JsonResponse
    {
        $residentKeyFinanceDateService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_key_finance_date_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_key_finance_date", level="DELETE")
     *
     * @param Request $request
     * @param ResidentKeyFinanceDateService $residentKeyFinanceDateService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentKeyFinanceDateService $residentKeyFinanceDateService): JsonResponse
    {
        $residentKeyFinanceDateService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_key_finance_date_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentKeyFinanceDateService $residentKeyFinanceDateService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentKeyFinanceDateService $residentKeyFinanceDateService): JsonResponse
    {
        $relatedData = $residentKeyFinanceDateService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
