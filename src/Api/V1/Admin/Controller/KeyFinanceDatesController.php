<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\KeyFinanceDatesService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\KeyFinanceDates;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/key-finance-dates")
 *
 * @Grant(grant="persistence-common-key_finance_dates", level="VIEW")
 *
 * Class KeyFinanceDatesController
 * @package App\Api\V1\Admin\Controller
 */
class KeyFinanceDatesController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_key_finance_dates_grid", methods={"GET"})
     *
     * @param Request $request
     * @param KeyFinanceDatesService $keyFinanceDatesService
     * @return JsonResponse
     */
    public function gridAction(Request $request, KeyFinanceDatesService $keyFinanceDatesService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            KeyFinanceDates::class,
            'api_admin_key_finance_dates_grid',
            $keyFinanceDatesService
        );
    }

    /**
     * @Route("/grid", name="api_admin_key_finance_dates_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, KeyFinanceDates::class, 'api_admin_key_finance_dates_grid');
    }

    /**
     * @Route("", name="api_admin_key_finance_dates_list", methods={"GET"})
     *
     * @param Request $request
     * @param KeyFinanceDatesService $keyFinanceDatesService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, KeyFinanceDatesService $keyFinanceDatesService)
    {
        return $this->respondList(
            $request,
            KeyFinanceDates::class,
            'api_admin_key_finance_dates_list',
            $keyFinanceDatesService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_key_finance_dates_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param KeyFinanceDatesService $keyFinanceDatesService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, KeyFinanceDatesService $keyFinanceDatesService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $keyFinanceDatesService->getById($id),
            ['api_admin_key_finance_dates_get']
        );
    }

    /**
     * @Route("", name="api_admin_key_finance_dates_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-key_finance_dates", level="ADD")
     *
     * @param Request $request
     * @param KeyFinanceDatesService $keyFinanceDatesService
     * @return JsonResponse
     */
    public function addAction(Request $request, KeyFinanceDatesService $keyFinanceDatesService): JsonResponse
    {
        $id = $keyFinanceDatesService->add(
            [
                'type' => $request->get('type'),
                'title' => $request->get('title'),
                'date' => $request->get('date'),
                'description' => $request->get('description') ?? '',
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_key_finance_dates_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-key_finance_dates", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param KeyFinanceDatesService $keyFinanceDatesService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, KeyFinanceDatesService $keyFinanceDatesService): JsonResponse
    {
        $keyFinanceDatesService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'date' => $request->get('date'),
                'description' => $request->get('description') ?? '',
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_key_finance_dates_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-key_finance_dates", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param KeyFinanceDatesService $keyFinanceDatesService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, KeyFinanceDatesService $keyFinanceDatesService): JsonResponse
    {
        $keyFinanceDatesService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_key_finance_dates_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-key_finance_dates", level="DELETE")
     *
     * @param Request $request
     * @param KeyFinanceDatesService $keyFinanceDatesService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, KeyFinanceDatesService $keyFinanceDatesService): JsonResponse
    {
        $keyFinanceDatesService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_key_finance_dates_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param KeyFinanceDatesService $keyFinanceDatesService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, KeyFinanceDatesService $keyFinanceDatesService): JsonResponse
    {
        $relatedData = $keyFinanceDatesService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
