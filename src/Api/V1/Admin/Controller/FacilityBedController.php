<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\FacilityBedService;
use App\Api\V1\Common\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/v1.0/admin/facility/bed")
 *
 * @Grant(grant="persistence-facility_bed", level="VIEW")
 *
 * Class FacilityBedController
 * @package App\Api\V1\Admin\Controller
 */
class FacilityBedController extends BaseController
{
    /**
     * @Route("/related/info", name="api_admin_facility_bed_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param FacilityBedService $facilityBedService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, FacilityBedService $facilityBedService): JsonResponse
    {
        $relatedData = $facilityBedService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
