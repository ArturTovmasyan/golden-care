<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Admin\Service\ReportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

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
 * @Route("/api/v1.0/admin/report")
 *
 * Class ReportController
 * @package App\Api\V1\Admin\Controller
 */
class ReportController extends BaseController
{
    /**
     * @Route("/list", name="api_admin_report_list", methods={"GET"})
     *
     * @param Request $request
     * @param ReportService $reportService
     * @return JsonResponse
     */
    public function listAction(Request $request, ReportService $reportService)
    {
        $data = $reportService->list();

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $data
        );
    }

    /**
     * @Route("/{group}/{alias}", requirements={"group"="[a-z0-9-]+", "alias"="[a-z0-9-]+"}, name="api_admin_report", methods={"GET"})
     *
     * @param Request $request
     * @param $group
     * @param $alias
     * @param ReportService $reportService
     * @return PdfResponse
     * @throws \Throwable
     */
    public function getAction(Request $request, $group, $alias, ReportService $reportService)
    {
        return $this->respondReport(
            $request,
            $group,
            $alias,
            $reportService
        );
    }
}
