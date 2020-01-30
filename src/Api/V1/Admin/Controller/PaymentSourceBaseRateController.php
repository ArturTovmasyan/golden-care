<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\PaymentSourceBaseRateService;
use App\Api\V1\Common\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/v1.0/admin/source-base-rate")
 *
 * @Grant(grant="persistence-common-source_base_rate", level="VIEW")
 *
 * Class PaymentSourceBaseRateController
 * @package App\Api\V1\Admin\Controller
 */
class PaymentSourceBaseRateController extends BaseController
{
    /**
     * @Route("/related/info", name="api_admin_source_base_rate_related_info", methods={"POST"})
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