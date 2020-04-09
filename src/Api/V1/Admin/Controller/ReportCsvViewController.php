<?php

namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Admin\Service\ReportService;
use App\Api\V1\Common\Service\Exception\CsvReportHashHasExpiredException;
use App\Api\V1\Common\Service\Exception\CsvReportNotFoundException;
use App\Entity\ReportCsvView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
 * @Route("/api/v1.0/report/csv-view")
 *
 * Class ReportCsvViewController
 * @package App\Api\V1\Admin\Controller
 */
class ReportCsvViewController extends BaseController
{
    /**
     * @Route("/{hash}", requirements={"hash"="[a-z0-9]+"}, name="api_admin_report_csv_view", methods={"GET"})
     *
     * @param Request $request
     * @param hash
     * @param ReportService $reportService
     * @return PdfResponse|Response
     */
    public function getAction(Request $request, $hash, ReportService $reportService)
    {
        /** @var ReportCsvView $reportCsvView */
        $reportCsvView = $this->em->getRepository(ReportCsvView::class)->findOneBy(['hash' => $hash]);

        if ($reportCsvView === null) {
            throw new CsvReportNotFoundException();
        }

        $now = new \DateTime('now');
        $now = strtotime($now->format('Y-m-d H:i:s'));
        if ($now > $reportCsvView->getExpiresAt()) {
            throw new CsvReportHashHasExpiredException();
        }

        $params = $reportCsvView->getParams();
        $group = $params['group'];
        $alias = $params['alias'];
        unset ($params['group'], $params['alias']);

        foreach ($params as $key => $param) {
            $request->query->set($key, $param);
        }

        $request->query->set('hash', $hash);

        return $this->respondReport(
            $request,
            $group,
            $alias,
            true,
            $reportService
        );
    }
}
