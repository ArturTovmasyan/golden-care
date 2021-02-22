<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\ReportLogService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\ReportLog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/v1.0/admin/report-log")
 *
 * @Grant(grant="persistence-security-report_log", level="VIEW")
 *
 * Class ReportLogController
 * @package App\Api\V1\Admin\Controller
 */
class ReportLogController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_report_log", methods={"GET"})
     *
     * @param Request $request
     * @param ReportLogService $reportLogService
     * @return JsonResponse
     */
    public function gridAction(Request $request, ReportLogService $reportLogService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            ReportLog::class,
            'api_admin_report_log_grid',
            $reportLogService
        );
    }

    /**
     * @Route("/grid", name="api_admin_report_log_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, ReportLog::class, 'api_admin_report_log_grid');
    }
}
