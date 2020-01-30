<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\RentReasonService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\RentReason;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/rent-reason")
 *
 * @Grant(grant="persistence-common-rent_reason", level="VIEW")
 *
 * Class RentReasonController
 * @package App\Api\V1\Admin\Controller
 */
class RentReasonController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_rent_reason_grid", methods={"GET"})
     *
     * @param Request $request
     * @param RentReasonService $rentReasonService
     * @return JsonResponse
     */
    public function gridAction(Request $request, RentReasonService $rentReasonService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            RentReason::class,
            'api_admin_rent_reason_grid',
            $rentReasonService
        );
    }

    /**
     * @Route("/grid", name="api_admin_rent_reason_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, RentReason::class, 'api_admin_rent_reason_grid');
    }

    /**
     * @Route("", name="api_admin_rent_reason_list", methods={"GET"})
     *
     * @param Request $request
     * @param RentReasonService $rentReasonService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, RentReasonService $rentReasonService)
    {
        return $this->respondList(
            $request,
            RentReason::class,
            'api_admin_rent_reason_list',
            $rentReasonService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_rent_reason_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param RentReasonService $rentReasonService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, RentReasonService $rentReasonService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $rentReasonService->getById($id),
            ['api_admin_rent_reason_get']
        );
    }

    /**
     * @Route("", name="api_admin_rent_reason_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-rent_reason", level="ADD")
     *
     * @param Request $request
     * @param RentReasonService $rentReasonService
     * @return JsonResponse
     */
    public function addAction(Request $request, RentReasonService $rentReasonService): JsonResponse
    {
        $id = $rentReasonService->add(
            [
                'title' => $request->get('title'),
                'notes' => $request->get('notes') ?? '',
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_rent_reason_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-rent_reason", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param RentReasonService $rentReasonService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, RentReasonService $rentReasonService): JsonResponse
    {
        $rentReasonService->edit(
            $id,
            [
                'title' => $request->get('title'),
                'notes' => $request->get('notes') ?? '',
                'space_id' => $request->get('space_id')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_rent_reason_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-rent_reason", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param RentReasonService $rentReasonService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, RentReasonService $rentReasonService): JsonResponse
    {
        $rentReasonService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_rent_reason_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-rent_reason", level="DELETE")
     *
     * @param Request $request
     * @param RentReasonService $rentReasonService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, RentReasonService $rentReasonService): JsonResponse
    {
        $rentReasonService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_rent_reason_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param RentReasonService $rentReasonService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, RentReasonService $rentReasonService): JsonResponse
    {
        $relatedData = $rentReasonService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
