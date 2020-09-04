<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\RpPaymentTypeService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\RpPaymentType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/rp-payment-type")
 *
 * @Grant(grant="persistence-common-rp_payment_type", level="VIEW")
 *
 * Class RpPaymentTypeController
 * @package App\Api\V1\Admin\Controller
 */
class RpPaymentTypeController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_rp_payment_type_grid", methods={"GET"})
     *
     * @param Request $request
     * @param RpPaymentTypeService $rpPaymentTypeService
     * @return JsonResponse
     */
    public function gridAction(Request $request, RpPaymentTypeService $rpPaymentTypeService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            RpPaymentType::class,
            'api_admin_rp_payment_type_grid',
            $rpPaymentTypeService
        );
    }

    /**
     * @Route("/grid", name="api_admin_rp_payment_type_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, RpPaymentType::class, 'api_admin_rp_payment_type_grid');
    }

    /**
     * @Route("", name="api_admin_rp_payment_type_list", methods={"GET"})
     *
     * @param Request $request
     * @param RpPaymentTypeService $rpPaymentTypeService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, RpPaymentTypeService $rpPaymentTypeService)
    {
        return $this->respondList(
            $request,
            RpPaymentType::class,
            'api_admin_rp_payment_type_list',
            $rpPaymentTypeService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_rp_payment_type_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param RpPaymentTypeService $rpPaymentTypeService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, RpPaymentTypeService $rpPaymentTypeService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $rpPaymentTypeService->getById($id),
            ['api_admin_rp_payment_type_get']
        );
    }

    /**
     * @Route("", name="api_admin_rp_payment_type_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-rp_payment_type", level="ADD")
     *
     * @param Request $request
     * @param RpPaymentTypeService $rpPaymentTypeService
     * @return JsonResponse
     */
    public function addAction(Request $request, RpPaymentTypeService $rpPaymentTypeService): JsonResponse
    {
        $id = $rpPaymentTypeService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_rp_payment_type_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-rp_payment_type", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param RpPaymentTypeService $rpPaymentTypeService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, RpPaymentTypeService $rpPaymentTypeService): JsonResponse
    {
        $rpPaymentTypeService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_rp_payment_type_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-rp_payment_type", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param RpPaymentTypeService $rpPaymentTypeService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, RpPaymentTypeService $rpPaymentTypeService): JsonResponse
    {
        $rpPaymentTypeService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_rp_payment_type_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-rp_payment_type", level="DELETE")
     *
     * @param Request $request
     * @param RpPaymentTypeService $rpPaymentTypeService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, RpPaymentTypeService $rpPaymentTypeService): JsonResponse
    {
        $rpPaymentTypeService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_rp_payment_type_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param RpPaymentTypeService $rpPaymentTypeService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, RpPaymentTypeService $rpPaymentTypeService): JsonResponse
    {
        $relatedData = $rpPaymentTypeService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
