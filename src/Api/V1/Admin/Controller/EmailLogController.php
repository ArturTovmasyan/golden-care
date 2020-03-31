<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\EmailLogService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\EmailLog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/v1.0/admin/email-log")
 *
 * @Grant(grant="persistence-common-email_log", level="VIEW")
 *
 * Class EmailLogController
 * @package App\Api\V1\Admin\Controller
 */
class EmailLogController extends BaseController
{
    /**
     * @Route("/grid", name="api_admin_email_log", methods={"GET"})
     *
     * @param Request $request
     * @param EmailLogService $activityService
     * @return JsonResponse
     */
    public function gridAction(Request $request, EmailLogService $activityService): JsonResponse
    {
        return $this->respondGrid(
            $request,
            EmailLog::class,
            'api_admin_email_log_grid',
            $activityService
        );
    }

    /**
     * @Route("/grid", name="api_admin_email_log_grid_options", methods={"OPTIONS"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function gridOptionAction(Request $request): JsonResponse
    {
        return $this->getOptionsByGroupName($request, EmailLog::class, 'api_admin_email_log_grid');
    }
}
