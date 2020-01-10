<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ApartmentBedService;
use App\Api\V1\Common\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

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
