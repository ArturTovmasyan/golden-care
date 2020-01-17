<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ApartmentBedService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ApartmentBed;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/admin/apartment/bed")
 *
 * @Grant(grant="persistence-apartment_bed", level="VIEW")
 *
 * Class ApartmentBedController
 * @package App\Api\V1\Admin\Controller
 */
class ApartmentBedController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_apartment_bed_grid", methods={"GET"})
     *
     * @param Request $request
     * @param ApartmentBedService $apartmentBedService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ApartmentBedService $apartmentBedService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ApartmentBed::class,
            'api_admin_apartment_bed_grid',
            $apartmentBedService,
            ['apartment_id' => $request->get('apartment_id')]
        );
    }

    /**
     * @Route("/grid", name="api_admin_apartment_bed_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ApartmentBed::class, 'api_admin_apartment_bed_grid');
    }

    /**
     * @Route("", name="api_admin_apartment_bed_list", methods={"GET"})
     *
     * @param Request $request
     * @param ApartmentBedService $apartmentBedService
     * @return PdfResponse|JsonResponse|Response
     */
    public function listAction(Request $request, ApartmentBedService $apartmentBedService)
    {
        return $this->respondList(
            $request,
            ApartmentBed::class,
            'api_admin_apartment_bed_list',
            $apartmentBedService,
            [
                'apartment_id' => $request->get('apartment_id')
            ]
        );
    }

    /**
     * @Route("/related/info", name="api_admin_apartment_bed_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ApartmentBedService $apartmentBedService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, ApartmentBedService $apartmentBedService): JsonResponse
    {
        $relatedData = $apartmentBedService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
