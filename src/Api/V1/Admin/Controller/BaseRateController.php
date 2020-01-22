<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\BaseRateService;
use App\Api\V1\Common\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/v1.0/admin/base-rate")
 *
 * @Grant(grant="persistence-base_rate", level="VIEW")
 *
 * Class BaseRateController
 * @package App\Api\V1\Admin\Controller
 */
class BaseRateController extends BaseController
{
    /**
     * @Route("/related/info", name="api_admin_base_rate_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param BaseRateService $baseRateService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, BaseRateService $baseRateService): JsonResponse
    {
        $relatedData = $baseRateService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
