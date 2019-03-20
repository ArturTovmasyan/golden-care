<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\ApartmentBedService;
use App\Api\V1\Common\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Annotation\Grant as Grant;

/**
 * @IgnoreAnnotation("api")
 * @IgnoreAnnotation("apiVersion")
 * @IgnoreAnnotation("apiName")
 * @IgnoreAnnotation("apiGroup")
 * @IgnoreAnnotation("apiDescription")
 * @IgnoreAnnotation("apiHeader")
 * @IgnoreAnnotation("apiSuccess")
 * @IgnoreAnnotation("apiSuccessExample")
 * @IgnoreAnnotation("apiParam")
 * @IgnoreAnnotation("apiParamExample")
 * @IgnoreAnnotation("apiErrorExample")
 * @IgnoreAnnotation("apiPermission")
 *
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
     * @api {post} /api/v1.0/admin/apartment/bed/related/info ApartmentBed related info
     * @apiVersion 1.0.0
     * @apiName ApartmentBed Related Info
     * @apiGroup Admin ApartmentBed
     * @apiDescription This function is used to get apartmentBed related info
     *
     * @apiHeader {String} Content-Type  application/x-www-form-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the apartmentBeds
     *
     * @apiParamExample {json} Request-Example:
     *     ["2", "1", "5"]
     *
     * @apiSuccessExample {json} Sample Response:
     *     HTTP/1.1 204 No Content
     *     {}
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *          "code": 624,
     *          "error": "ApartmentBed not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_apartment_bed_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param ApartmentBedService $apartmentBedService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, ApartmentBedService $apartmentBedService)
    {
        $relatedData = $apartmentBedService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
