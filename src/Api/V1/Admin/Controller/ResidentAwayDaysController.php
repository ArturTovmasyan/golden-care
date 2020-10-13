<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ResidentAwayDaysService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ResidentAwayDays;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/resident-away-days")
 *
 * @Grant(grant="persistence-resident-resident_away_days", level="VIEW")
 *
 * Class ResidentAwayDaysController
 * @package App\Api\V1\Admin\Controller
 */
class ResidentAwayDaysController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_resident_away_days_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAwayDaysService $residentAwayDaysService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ResidentAwayDaysService $residentAwayDaysService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ResidentAwayDays::class,
            'api_admin_resident_away_days_grid',
            $residentAwayDaysService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_resident_away_days_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ResidentAwayDays::class, 'api_admin_resident_away_days_grid');
    }

    /**
     * @Route("", name="api_admin_resident_away_days_list", methods={"GET"})
     *
     * @param Request $request
     * @param ResidentAwayDaysService $residentAwayDaysService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ResidentAwayDaysService $residentAwayDaysService)
    {
        return $this->respondList(
            $request,
            ResidentAwayDays::class,
            'api_admin_resident_away_days_list',
            $residentAwayDaysService,
            ['ledger_id' => $request->get('ledger_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_away_days_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param ResidentAwayDaysService $residentAwayDaysService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, ResidentAwayDaysService $residentAwayDaysService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $residentAwayDaysService->getById($id),
            ['api_admin_resident_away_days_get']
        );
    }

    /**
     * @Route("", name="api_admin_resident_away_days_add", methods={"POST"})
     *
     * @Grant(grant="persistence-resident-resident_away_days", level="ADD")
     *
     * @param Request $request
     * @param ResidentAwayDaysService $residentAwayDaysService
     * @return JsonResponse
     */
    public function addAction(Request $request, ResidentAwayDaysService $residentAwayDaysService): JsonResponse
    {
        $id = $residentAwayDaysService->add(
            [
                'ledger_id' => $request->get('ledger_id'),
                'start' => $request->get('start'),
                'end' => $request->get('end'),
                'reason' => $request->get('reason'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_away_days_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-resident-resident_away_days", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param ResidentAwayDaysService $residentAwayDaysService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, ResidentAwayDaysService $residentAwayDaysService): JsonResponse
    {
        $residentAwayDaysService->edit(
            $id,
            [
                'ledger_id' => $request->get('ledger_id'),
                'start' => $request->get('start'),
                'end' => $request->get('end'),
                'reason' => $request->get('reason'),
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_resident_away_days_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_away_days", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param ResidentAwayDaysService $residentAwayDaysService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, ResidentAwayDaysService $residentAwayDaysService): JsonResponse
    {
        $residentAwayDaysService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_resident_away_days_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-resident-resident_away_days", level="DELETE")
     *
     * @param Request $request
     * @param ResidentAwayDaysService $residentAwayDaysService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, ResidentAwayDaysService $residentAwayDaysService): JsonResponse
    {
        $residentAwayDaysService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_resident_away_days_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ResidentAwayDaysService $residentAwayDaysService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ResidentAwayDaysService $residentAwayDaysService): JsonResponse
    {
        $relatedData = $residentAwayDaysService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
