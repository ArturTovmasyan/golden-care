<?php

namespace App\Api\V1\Admin\Controller;

use App\Api\V1\Common\Controller\BaseController;
use App\Api\V1\Admin\Service\ReportService;
use App\Entity\ReportCsvView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;

/**
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
    public function listAction(Request $request, ReportService $reportService): JsonResponse
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
     * @return PdfResponse|Response|string
     * @throws \Exception
     */
    public function getAction(Request $request, $group, $alias, ReportService $reportService)
    {
        if (!empty($request->get('hash') && (bool)$request->get('hash') === true)) {
            try {
                $this->em->getConnection()->beginTransaction();

                $hash = hash('sha256', (random_bytes(32)) . time());
                $expiresAt = strtotime(date('Y-m-d H:i:s', strtotime('+1 hour')));
                $getParams = $request->query->all();
                if (array_key_exists('hash', $getParams)) {
                    unset($getParams['hash']);
                }
                $params = array_merge(['group' => $group, 'alias' => $alias], $getParams);

                $reportCsvView = new ReportCsvView();
                $reportCsvView->setHash($hash);
                $reportCsvView->setExpiresAt($expiresAt);
                $reportCsvView->setParams($params);

                $this->em->persist($reportCsvView);

                $this->em->flush();

                $this->em->getConnection()->commit();

                $this->saveReportLog($params['group'] . '-' . $params['alias'], $params['format']);

                return $this->respondSuccess(
                    Response::HTTP_OK,
                    '',
                    [$reportCsvView->getHash()]
                );
            } catch (\Exception $e) {
                $this->em->getConnection()->rollBack();

                throw $e;
            }
        } else {
            return $this->respondReport(
                $request,
                $group,
                $alias,
                false,
                $reportService
            );
        }
    }
}
