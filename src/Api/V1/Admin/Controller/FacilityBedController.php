<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\FacilityBedService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\FacilityBed;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

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
     * @Route("/grid", name="api_admin_facility_bed_grid", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityBedService $facilityBedService
     * @return JsonResponse
     */
    public function gridAction(Request $request, FacilityBedService $facilityBedService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            FacilityBed::class,
            'api_admin_facility_bed_grid',
            $facilityBedService,
            ['facility_id' => $request->get('facility_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_facility_bed_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, FacilityBed::class, 'api_admin_facility_bed_grid');
    }

    /**
     * @Route("", name="api_admin_facility_bed_list", methods={"GET"})
     *
     * @param Request $request
     * @param FacilityBedService $facilityBedService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, FacilityBedService $facilityBedService)
    {
        return $this->respondList(
            $request,
            FacilityBed::class,
            'api_admin_facility_bed_list',
            $facilityBedService,
            [
                'facility_id' => $request->get('facility_id')
            ]
        );
    }

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
