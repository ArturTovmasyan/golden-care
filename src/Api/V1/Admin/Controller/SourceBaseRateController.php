<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\SourceBaseRateService;
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
 * Class SourceBaseRateController
 * @package App\Api\V1\Admin\Controller
 */
class SourceBaseRateController extends BaseController
{
    /**
     * @Route("/related/info", name="api_admin_source_base_rate_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param SourceBaseRateService $sourceBaseRateService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, SourceBaseRateService $sourceBaseRateService): JsonResponse
    {
        $relatedData = $sourceBaseRateService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}