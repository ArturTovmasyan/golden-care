<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\LatePaymentService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\LatePayment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/late-payment")
 *
 * @Grant(grant="persistence-common-late_payment", level="VIEW")
 *
 * Class LatePaymentController
 * @package App\Api\V1\Admin\Controller
 */
class LatePaymentController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_late_payment_grid", methods={"GET"})
     *
     * @param Request $request
     * @param LatePaymentService $latePaymentService
     * @return JsonResponse
     */
    public function gridAction(Request $request, LatePaymentService $latePaymentService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            LatePayment::class,
            'api_admin_late_payment_grid',
            $latePaymentService
        );
    }

    /**
     * @Route("/grid", name="api_admin_late_payment_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, LatePayment::class, 'api_admin_late_payment_grid');
    }

    /**
     * @Route("", name="api_admin_late_payment_list", methods={"GET"})
     *
     * @param Request $request
     * @param LatePaymentService $latePaymentService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, LatePaymentService $latePaymentService)
    {
        return $this->respondList(
            $request,
            LatePayment::class,
            'api_admin_late_payment_list',
            $latePaymentService
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_late_payment_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param LatePaymentService $latePaymentService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, LatePaymentService $latePaymentService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $latePaymentService->getById($id),
            ['api_admin_late_payment_get']
        );
    }

    /**
     * @Route("", name="api_admin_late_payment_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-late_payment", level="ADD")
     *
     * @param Request $request
     * @param LatePaymentService $latePaymentService
     * @return JsonResponse
     */
    public function addAction(Request $request, LatePaymentService $latePaymentService): JsonResponse
    {
        $id = $latePaymentService->add(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_late_payment_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-late_payment", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param LatePaymentService $latePaymentService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, LatePaymentService $latePaymentService): JsonResponse
    {
        $latePaymentService->edit(
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
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_late_payment_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-late_payment", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param LatePaymentService $latePaymentService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, LatePaymentService $latePaymentService): JsonResponse
    {
        $latePaymentService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_late_payment_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-late_payment", level="DELETE")
     *
     * @param Request $request
     * @param LatePaymentService $latePaymentService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, LatePaymentService $latePaymentService): JsonResponse
    {
        $latePaymentService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_late_payment_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param LatePaymentService $latePaymentService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, LatePaymentService $latePaymentService): JsonResponse
    {
        $relatedData = $latePaymentService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
