<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentLedgerService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentLedger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident/ledger")
 *
 * @Grant(grant="persistence-resident-resident_ledger", level="VIEW")
 *
 * Class ResidentLedgerController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentLedgerController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_ledger_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentLedgerService $residentLedgerService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentLedgerService $residentLedgerService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentLedger::class,
            'api_admin_resident_ledger_grid',
            $residentLedgerService,
            [
                'resident_id' => $request->get('resident_id')
            ]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_ledger_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentLedger::class, 'api_admin_resident_ledger_grid');
    }

    /**
     * @Route("", name="api_admin_resident_ledger_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentLedgerService $residentLedgerService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentLedgerService $residentLedgerService)
    {
        return $this->respondList(
            $request,
            ResidentLedger::class,
            'api_admin_resident_ledger_list',
            $residentLedgerService,
            [
                'resident_id' => $request->get('resident_id')
            ]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_ledger_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentLedgerService $residentLedgerService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentLedgerService $residentLedgerService): JsonResponse
    {
        $gridData = $this->respondQueryBuilderResult(
            $request,
            ResidentLedger::class,
            'api_admin_resident_ledger_grid',
            $residentLedgerService,
            ['resident_id' => $request->get('resident_id')]
        );

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentLedgerService->getById($id, $gridData),
            ['api_admin_resident_ledger_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_ledger_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_ledger", level="ADD")
     *
     * @param Request $request
     * @param ResidentLedgerService $residentLedgerService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentLedgerService $residentLedgerService): JsonResponse
    {
        $id = $residentLedgerService->add(
            [
                'resident_id' => $request->get('resident_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_ledger_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_ledger", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentLedgerService $residentLedgerService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentLedgerService $residentLedgerService): JsonResponse
    {
        $residentLedgerService->edit(
            $id,
            [
                'resident_id' => $request->get('resident_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_ledger_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_ledger", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentLedgerService $residentLedgerService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentLedgerService $residentLedgerService): JsonResponse
    {
        $residentLedgerService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_ledger_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_ledger", level="DELETE")
     *
     * @param Request $request
     * @param ResidentLedgerService $residentLedgerService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentLedgerService $residentLedgerService): JsonResponse
    {
        $residentLedgerService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_ledger_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentLedgerService $residentLedgerService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentLedgerService $residentLedgerService): JsonResponse
    {
        $relatedData = $residentLedgerService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}