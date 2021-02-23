<?php

namespace App\Api\V1\Common\Controller;

use App\Annotation\Grid;
use App\Api\V1\Admin\Service\ReportService;
use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\Exception\GridOptionsNotFoundException;
use App\Api\V1\Common\Service\Exception\ResourceNotFoundException;
use App\Api\V1\Common\Service\GrantService;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\ReportLog;
use App\Entity\Space;
use App\Entity\UserInvite;
use App\Model\Grant;
use App\Model\Report;
use App\Util\ArrayUtil;
use App\Util\Mailer;
use App\Util\MimeUtil;
use App\Util\StringUtil;
use Doctrine\Common\Annotations\Reader;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use GuzzleHttp\Psr7\Stream;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;
use function array_values;
use function in_array;
use function strlen;

class BaseController extends AbstractController
{
    /** @var SerializerInterface */
    protected $serializer;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var UserPasswordEncoderInterface */
    protected $encoder;

    /** @var Reader */
    protected $reader;

    /** @var Pdf */
    protected $pdf;

    /** @var Mailer */
    protected $mailer;

    /** @var Security */
    protected $security;

    /** @var GrantService */
    protected $grantService;

    /** @var ReportService */
    protected $reportService;

    /**
     * BaseController constructor.
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @param Reader $reader
     * @param Pdf $pdf
     * @param Mailer $mailer
     * @param Security $security
     * @param GrantService $grantService
     * @param ReportService $reportService
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder,
        Reader $reader,
        Pdf $pdf,
        Mailer $mailer,
        Security $security,
        GrantService $grantService,
        ReportService $reportService
    )
    {
        $this->serializer = $serializer;
        $this->em = $em;
        $this->validator = $validator;
        $this->encoder = $encoder;
        $this->reader = $reader;
        $this->pdf = $pdf;
        $this->mailer = $mailer;
        $this->security = $security;
        $this->grantService = $grantService;
        $this->reportService = $reportService;
    }

    /**
     * @param Request $request
     * @param string $entityName
     * @param string $groupName
     * @param IGridService $service
     * @param mixed ...$params
     * @return PdfResponse|JsonResponse|Response|StreamedResponse
     * @throws ConnectionException
     * @throws Exception
     * @throws ReflectionException
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function respondList(Request $request, string $entityName, string $groupName, IGridService $service, ...$params)
    {
        if ($request->get('pdf')) {
            $fields = $this->getGrid($entityName)->getGroupOptions($groupName);

            // TODO(haykg): this is temporary solution, need review
            foreach ($fields as &$field) {
                $field['id'] = preg_replace_callback('/(_\w)/', static function ($matches) {
                    return ucfirst($matches[1][1]);
                }, $field['id']);
            }

            return $this->respondPdf(
                $request,
                $service->list($params),
                $fields
            );
        }

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            $service->list($params),
            [$groupName]
        );
    }

    /**
     * @param Request $request
     * @param string $entityName
     * @param string $groupName
     * @param IGridService $service
     * @param mixed ...$params
     * @return JsonResponse
     * @throws Throwable
     */
    protected function respondGrid(Request $request, string $entityName, string $groupName, IGridService $service, ...$params): JsonResponse
    {
        $queryBuilder = $this->getQueryBuilder($request, $entityName, $groupName);
        $service->gridSelect($queryBuilder, $params);
        $this->getGrid($entityName)->renderByGroup($request->query->all(), $groupName, $this->gridIgnoreFields($request));

        $paginator = new Paginator($queryBuilder);

        $page = $request->get('page') ?: 1;
        $perPage = $request->get('per_page');

        $total = $paginator->count();

        $paginator
            ->getQuery()
            ->setFirstResult($perPage * ($page - 1))
            ->setMaxResults($perPage);

        $data = [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'data' => $paginator->getQuery()->getArrayResult()
        ];

        $this->getGrid($entityName)->renderCallback($data['data'], $groupName);

        $serializationContext = SerializationContext::create()->setSerializeNull(true);
        if (!empty($groupName)) {
            $serializationContext->setGroups([$groupName]);
        }
        $responseData = $this->serializer->serialize($data, 'json', $serializationContext);

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    /**
     * @param int $httpStatus
     * @param string $message
     * @param array $data
     * @param array $groups
     * @param array $headers
     * @return JsonResponse
     */
    protected function respondSuccess($httpStatus = Response::HTTP_OK, $message = '', $data = [], $groups = [], $headers = []): JsonResponse
    {
        $responseData = [];

        if (!empty($message)) {
            $responseData['message'] = $message;
        } elseif (isset(ResponseCode::$titles[$httpStatus])) {
            $responseData['code'] = $httpStatus;
            $responseData['message'] = ResponseCode::$titles[$httpStatus]['message'];
            $httpStatus = ResponseCode::$titles[$httpStatus]['httpCode'];
        }

        if (!empty($data)) {
            $responseData = $data;
        }

        $serializationContext = SerializationContext::create()->setSerializeNull(true);
        if (!empty($groups)) {
            $serializationContext->setGroups($groups);
        }
        $responseData = $this->serializer->serialize($data, 'json', $serializationContext);

        return new JsonResponse($responseData, $httpStatus, $headers, true);
    }

    /**
     * @param Request $request
     * @param $data
     * @param $fields
     * @return PdfResponse|Response|StreamedResponse
     * @throws ConnectionException
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function respondPdf(Request $request, $data, $fields)
    {
        return $this->respondFile(
            '@api_grid/grid.html.twig',
            'output',
            'pdf',
            [
                'title' => 'Title',
                'fields' => $fields,
                'data' => $data
            ]
        );
    }

    /**
     * @param $html
     * @param $actualName
     * @return Response
     */
    protected function respondCsv($html, $actualName): Response
    {
        return new Response($html, Response::HTTP_OK, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $actualName . '.csv"',
            'Content-Transfer-Encoding' => 'binary',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * @param Request $request
     * @param string $group
     * @param string $alias
     * @param string $format
     * @param array $params
     * @return Response
     */
    protected function respondCsvReport(Request $request, string $group, string $alias, $format = Report::FORMAT_CSV, array $params = []): Response
    {
        $file = '@api_report/' . $group . '/' . $alias . '.' . $format . '.twig';

        return $this->respondCsv($this->renderView($file, $params), $group . '-' . $alias);
    }

    /**
     * @param $html
     * @param $actualName
     * @param $params
     * @return StreamedResponse
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function respondExcel($html, $actualName, $params): StreamedResponse
    {
        $directory = 'excel/';

        if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        $hash = $params['hash'];

        $fileNameCsv = $hash . '.csv';
        $fileUrlCsv = $directory . $fileNameCsv;
        $fp = fopen($fileUrlCsv, 'ab');
        fputcsv($fp, (array)$html, $delimiter = ',', chr(0));
        fclose($fp);

        $fh = fopen($fileUrlCsv, 'rb');
        $current = trim(stream_get_contents($fh));
        fclose($fh);
        file_put_contents($fileUrlCsv, $current);

        $reader = new Csv();

        /* Set CSV parsing options */
        $reader->setDelimiter(',');
        $reader->setEnclosure('');
        $reader->setSheetIndex(0);

        /* Load a CSV file and save as a XLS */
        $spreadsheet = $reader->load($fileUrlCsv);
        $writer = new Xlsx($spreadsheet);
        foreach(range('A','Z') as $columnID) {
            $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }
        $fileName = $actualName . '.xlsx';

        $response =  new StreamedResponse(
            static function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control','max-age=0');

        if (file_exists($fileUrlCsv)) {
            unlink($fileUrlCsv);
        }

        return $response;
    }

    /**
     * @param $template
     * @param $actualName
     * @param string $format
     * @param array $params
     * @return PdfResponse|Response|StreamedResponse
     * @throws ConnectionException
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function respondFile($template, $actualName, $format = Report::FORMAT_PDF, array $params = [])
    {
        $options = [];

        if (property_exists($params['data'], 'options')) {
            $options = $params['data']->getOptions();
        }

        $html = $this->renderView($template, $params);

        if ($format === Report::FORMAT_PDF) {
            $this->saveReportLog($actualName, $format);
            return new PdfResponse($this->pdf->getOutputFromHtml($html, $options), $actualName . '.pdf');
        }

        if ($format === Report::FORMAT_CSV) {
            $this->saveReportLog($actualName, $format);
            return $this->respondCsv($html, $actualName);
        }

        if ($format === Report::FORMAT_XLS) {
            $this->saveReportLog($actualName, $format);
            return $this->respondExcel($html, $actualName, $params);
        }

        throw new RuntimeException('Support only pdf, csv and xls formats');
    }

    /**
     * @param Request $request
     * @param string $group
     * @param string $alias
     * @param bool $isHash
     * @param ReportService $reportService
     * @return PdfResponse|Response|StreamedResponse
     * @throws ConnectionException
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function respondReport(Request $request, string $group, string $alias, bool $isHash, ReportService $reportService)
    {
        $report = $reportService->report($request, $group, $alias, $isHash);

        $format = !empty($request->get('format')) && $request->get('format') !== Report::FORMAT_XLS ? $request->get('format') : Report::FORMAT_CSV;

        if ($request->get('template')) {
            $file = '@api_report/' . $group . '/' . $request->get('template') . '.' . $format . '.twig';
        } else {
            $file = '@api_report/' . $group . '/' . $alias . '.' . $format . '.twig';
        }

        return $this->respondFile(
            $file,
            $group . '-' . $alias,
            $request->get('format'),
            [
                'data' => $report,
                'hash' => $request->get('hash')
            ]
        );
    }

    /**
     * @param Request $request
     * @param string $entityName
     * @param string $groupName
     * @return JsonResponse
     * @throws ReflectionException
     */
    protected function getOptionsByGroupName(Request $request, string $entityName, string $groupName): JsonResponse
    {
        $options = $this->getGrid($entityName)->getGroupOptions($groupName);

        if (!$options) {
            throw new GridOptionsNotFoundException();
        }

        $ignoreFields = $this->gridIgnoreFields($request);

        if (!empty($ignoreFields)) {
            foreach ($options as $key => $option) {
                if (in_array($option['id'], $ignoreFields, false)) {
                    unset($options[$key]);
                }
            }

            $options = array_values($options);
        }

        if (!$this->grantService->hasCurrentUserEntityGrant(Space::class, Grant::$LEVEL_VIEW)) {
            $options = array_values(array_filter($options, static function ($value) {
                return $value['id'] !== 'space';
            }));
        }

        $options = ArrayUtil::remove_keys($options, ['field']);

        // TODO: review
        $buttons = [
            'add' => $this->grantService->hasCurrentUserEntityGrant($entityName, Grant::$LEVEL_EDIT),
            'edit' => $entityName === UserInvite::class ? false : $this->grantService->hasCurrentUserEntityGrant($entityName, Grant::$LEVEL_EDIT),
            'remove' => $this->grantService->hasCurrentUserEntityGrant($entityName, Grant::$LEVEL_DELETE),
        ];

        return $this->respondSuccess(
            Response::HTTP_OK,
            '',
            ['buttons' => $buttons, 'fields' => $options]
        );
    }

    /**
     * @param Request $request
     * @param string $entityName
     * @param string $groupName
     * @return QueryBuilder
     * @throws ReflectionException
     */
    protected function getQueryBuilder(Request $request, string $entityName, string $groupName): QueryBuilder
    {
        return $this->getGrid($entityName)
            ->setEntityManager($this->em)
            ->setQueryBuilder($this->em->createQueryBuilder())
            ->getQueryBuilder();
    }

    /**
     * @param $entityName
     * @return null|object|Grid
     * @throws ReflectionException
     */
    private function getGrid($entityName)
    {
        /**
         * @var Grid $annotation
         */
        $reflectionClass = new ReflectionClass($entityName);

        return $this->reader->getClassAnnotation($reflectionClass, Grid::class);
    }

    /**
     * @param $title
     * @param $mimeType
     * @param $awsData
     * @return Response
     */
    protected function respondResource($title, $mimeType, $awsData): Response
    {
        /** @var Stream $stream */
        $stream = $awsData['Body'];

        if (!empty($awsData['Body'])) {
            $data = $stream->getContents();

            $stream->close();

            return new Response($data, Response::HTTP_OK, [
                'Content-Type' => $mimeType,
                'Content-Length' => strlen($data),
                'Content-Disposition' => 'attachment; filename="' . StringUtil::slugify($title) . '.' . MimeUtil::mime2ext($mimeType) . '"'
            ]);
        }

        throw new ResourceNotFoundException();
    }

    /**
     * @param $title
     * @param $mimeType
     * @param $data
     * @return Response
     */
    protected function respondImageFile($title, $mimeType, $data): Response
    {
        if (!empty($data)) {
            return new Response($data, Response::HTTP_OK, [
                'Content-Type' => $mimeType,
                'Content-Length' => strlen($data),
                'Content-Disposition' => 'attachment; filename="' . StringUtil::slugify($title) . '.' . MimeUtil::mime2ext($mimeType) . '"'
            ]);
        }

        throw new ResourceNotFoundException();
    }

    protected function gridIgnoreFields(Request $request): array
    {
        return [];
    }

    /**
     * @param Request $request
     * @param string $entityName
     * @param string $groupName
     * @param IGridService $service
     * @param mixed ...$params
     * @return int|mixed|string
     * @throws ReflectionException
     * @throws Throwable
     */
    protected function respondQueryBuilderResult(Request $request, string $entityName, string $groupName, IGridService $service, ...$params)
    {
        $queryBuilder = $this->getQueryBuilder($request, $entityName, $groupName);
        $service->gridSelect($queryBuilder, $params);
        $this->getGrid($entityName)->renderByGroup($request->query->all(), $groupName, $this->gridIgnoreFields($request));

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $actualName
     * @param $format
     * @throws ConnectionException
     */
    private function saveReportLog($actualName, $format): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $arrayActualName = explode('-', $actualName);
            $reportGroup = array_shift($arrayActualName);
            $reportAlias = implode('-', $arrayActualName);
            $reportTitle = $this->reportService->config[$reportGroup]['reports'][$reportAlias]['title'];

            $reportLog = new ReportLog();
            $reportLog->setTitle($reportTitle);
            $reportLog->setFormat($format);

            $this->em->persist($reportLog);
            $this->em->flush();

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}