<?php

namespace App\Api\V1\Admin\Controller;

use App\Annotation\Grant;
use App\Api\V1\Admin\Service\AssessmentRowService;
use App\Api\V1\Common\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api/v1.0/admin/assessment/row")
 *
 * @Grant(grant="persistence-assessment-row", level="VIEW")
 *
 * Class AssessmentRowController
 * @package App\Api\V1\Admin\Controller
 */
class AssessmentRowController extends BaseController
{
    /**
     * @Route("/related/info", name="api_admin_assessment_row_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AssessmentRowService $rowService
     * @return JsonResponse
     */
    public function relatedInfoAction(Request $request, AssessmentRowService $rowService): JsonResponse
    {
        $relatedData = $rowService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
