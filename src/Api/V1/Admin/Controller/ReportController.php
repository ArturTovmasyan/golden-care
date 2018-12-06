<?php
namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Admin\Service\AllergenService;
use App\Api\V1\Common\Controller\BaseController;
use App\Entity\Allergen;
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
     * @api {get} /api/v1.0/admin/report Get Report
     * @apiVersion 1.0.0
     * @apiName Get Report
     * @apiGroup Admin Report
     * @apiDescription This function is used to download any reports
     *
     * @apiHeader {String} Content-Type  application/json
     * @apiHeader {String} Authorization Bearer ACCESS_TOKEN
     *
     * @apiParam {String}  format  The identifier of the format (available pdf, csv)
     * @apiParam {Int}     type    The identifier of the report (1 - Filled, 2 - Blank), used for <code>Assessment</code>
     *
     * @Route("/{alias}", requirements={"alias"="\w+"}, name="api_admin_report", methods={"GET"})
     *
     * @param Request $request
     * @param $alias
     * @return PdfResponse
     * @throws \Exception
     */
    public function getAction(Request $request, $alias)
    {
        return $this->respondReport(
            $request,
            $alias
        );
    }
}
