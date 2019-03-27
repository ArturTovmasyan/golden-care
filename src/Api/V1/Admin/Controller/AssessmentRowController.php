<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\AssessmentRowService;
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
     * @api {post} /api/v1.0/admin/assessment/row/related/info AssessmentRow related info
     * @apiVersion 1.0.0
     * @apiName AssessmentRow Related Info
     * @apiGroup Admin AssessmentRows
     * @apiDescription This function is used to get row related info
     *
     * @apiHeader {String} Content-Type  application/x-www-row-urlencoded
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {Int[]} ids The unique identifier of the facilities
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
     *          "error": "AssessmentRow not found"
     *     }
     *
     * @Route("/related/info", name="api_admin_assessment_row_related_info", methods={"POST"})
     *
     * @param Request $request
     * @param AssessmentRowService $rowService
     * @return JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Throwable
     */
    public function relatedInfoAction(Request $request, AssessmentRowService $rowService)
    {
        $relatedData = $rowService->getRelatedInfo($request->get('ids'));

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            [$relatedData]
        );
    }
}
