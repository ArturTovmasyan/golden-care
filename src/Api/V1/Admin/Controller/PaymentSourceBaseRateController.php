<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\PaymentSourceBaseRateService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\PaymentSourceBaseRate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/v1.0/admin/payment-source-base-rate")
 *
 * @Grant(grant="persistence-common-payment_source_base_rate", level="VIEW")
 *
 * Class PaymentSourceBaseRateController
 * @package App\Api\V1\Admin\Controller
 */
class PaymentSourceBaseRateController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_payment_source_base_rate_grid", methods={"GET"})
     *
     * @param Request $request
     * @param PaymentSourceBaseRateService $paymentSourceBaseRateService
     * @return JsonResponse
     */
    public function gridAction(Request $request, PaymentSourceBaseRateService $paymentSourceBaseRateService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            PaymentSourceBaseRate::class,
            'api_admin_payment_source_base_rate_grid',
            $paymentSourceBaseRateService,
            ['payment_source_id' => $request->get('payment_source_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_payment_source_base_rate_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, PaymentSourceBaseRate::class, 'api_admin_payment_source_base_rate_grid');
    }

    /**
     * @Route("", name="api_admin_payment_source_base_rate_list", methods={"GET"})
     *
     * @param Request $request
     * @param PaymentSourceBaseRateService $paymentSourceBaseRateService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, PaymentSourceBaseRateService $paymentSourceBaseRateService)
    {
        return $this->respondList(
            $request,
            PaymentSourceBaseRate::class,
            'api_admin_payment_source_base_rate_list',
            $paymentSourceBaseRateService,
            ['payment_source_id' => $request->get('payment_source_id')]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_payment_source_base_rate_get", methods={"GET"})
     *
     * @param Request $request
     * @param $id
     * @param PaymentSourceBaseRateService $paymentSourceBaseRateService
     * @return JsonResponse
     */
    public function getAction(Request $request, $id, PaymentSourceBaseRateService $paymentSourceBaseRateService): JsonResponse
    {
        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $paymentSourceBaseRateService->getById($id),
            ['api_admin_payment_source_base_rate_get']
        );
    }

    /**
     * @Route("", name="api_admin_payment_source_base_rate_add", methods={"POST"})
     *
     * @Grant(grant="persistence-common-payment_source_base_rate", level="ADD")
     *
     * @param Request $request
     * @param PaymentSourceBaseRateService $paymentSourceBaseRateService
     * @return JsonResponse
     */
    public function addAction(Request $request, PaymentSourceBaseRateService $paymentSourceBaseRateService): JsonResponse
    {
        $id = $paymentSourceBaseRateService->add(
            [
                'payment_source_id' => $request->get('payment_source_id'),
                'date' => $request->get('date'),
                'base_rates' => $request->get('base_rates')

            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED,
            '',
            [$id]
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_payment_source_base_rate_edit", methods={"PUT"})
     *
     * @Grant(grant="persistence-common-payment_source_base_rate", level="EDIT")
     *
     * @param Request $request
     * @param $id
     * @param PaymentSourceBaseRateService $paymentSourceBaseRateService
     * @return JsonResponse
     */
    public function editAction(Request $request, $id, PaymentSourceBaseRateService $paymentSourceBaseRateService): JsonResponse
    {
        $paymentSourceBaseRateService->edit(
            $id,
            [
                'payment_source_id' => $request->get('payment_source_id'),
                'date' => $request->get('date'),
                'base_rates' => $request->get('base_rates')
            ]
        );

        return $this->respondSuccess(
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="api_admin_payment_source_base_rate_delete", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-payment_source_base_rate", level="DELETE")
     *
     * @param Request $request
     * @param $id
     * @param PaymentSourceBaseRateService $paymentSourceBaseRateService
     * @return JsonResponse
     */
    public function deleteAction(Request $request, $id, PaymentSourceBaseRateService $paymentSourceBaseRateService): JsonResponse
    {
        $paymentSourceBaseRateService->remove($id);

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("", name="api_admin_payment_source_base_rate_delete_bulk", methods={"DELETE"})
     *
     * @Grant(grant="persistence-common-payment_source_base_rate", level="DELETE")
     *
     * @param Request $request
     * @param PaymentSourceBaseRateService $paymentSourceBaseRateService
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, PaymentSourceBaseRateService $paymentSourceBaseRateService): JsonResponse
    {
        $paymentSourceBaseRateService->removeBulk($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route("/related/info", name="api_admin_payment_source_base_rate_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param PaymentSourceBaseRateService $paymentSourceBaseRateService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, PaymentSourceBaseRateService $paymentSourceBaseRateService): JsonResponse
    {
        $relatedData = $paymentSourceBaseRateService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}