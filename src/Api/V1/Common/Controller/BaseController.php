<?php

namespace App\Api\V1\Common\Controller;

use App\Annotation\Grid;
use App\Api\V1\Admin\Service\ReportService;
use App\Api\V1\Common\Model\ResponseCode;
use App\Api\V1\Common\Service\Exception\GridOptionsNotFoundException;
use App\Api\V1\Common\Service\GrantService;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Space;
use App\Entity\UserInvite;
use App\Model\Grant;
use App\Util\ArrayUtil;
use App\Util\Mailer;
use App\Util\MimeUtil;
use App\Util\StringUtil;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseController extends Controller
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
        GrantService $grantService
    ) {
        $this->serializer = $serializer;
        $this->em         = $em;
        $this->validator  = $validator;
        $this->encoder    = $encoder;
        $this->reader     = $reader;
        $this->pdf        = $pdf;
        $this->mailer     = $mailer;
        $this->security   = $security;
        $this->grantService   = $grantService;
    }

    /**
     * @param Request $request
     * @param string $entityName
     * @param string $groupName
     * @param IGridService $service
     * @return JsonResponse|PdfResponse
     * @throws \Exception
     */
    protected function respondList(Request $request, string $entityName, string $groupName, IGridService $service, ...$params)
    {
        if ($request->get('pdf')) {
            $fields = $this->getGrid($entityName)->getGroupOptions($groupName);

            // TODO(haykg): this is temporary solution, need review
            foreach ($fields as &$field) {
                $field['id'] = preg_replace_callback('/(_\w)/', function ($matches) {
                    return ucfirst($matches[1][1]);
                }, $field['id']);
            }

            return $this->respondPdf(
                $request,
                $service->list($params),
                $fields
            );
        } else {
            return $this->respondSuccess(
                Response::HTTP_OK,
                '',
                $service->list($params),
                [$groupName]
            );
        }
    }

    /**
     * @param Request $request
     * @param string $entityName
     * @param string $groupName
     * @param IGridService $service
     * @return JsonResponse|PdfResponse
     * @throws \ReflectionException
     */
    protected function respondGrid(Request $request, string $entityName, string $groupName, IGridService $service, ...$params)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $queryBuilder = $this->getQueryBuilder($request, $entityName, $groupName);
        $service->gridSelect($queryBuilder, $params);

        $paginator = new Paginator($queryBuilder);

        $page    = $request->get('page') ?: 1;
        $perPage = $request->get('per_page');

        $total   = $paginator->count();

        $paginator
            ->getQuery()
            ->setFirstResult($perPage * ($page-1))
            ->setMaxResults($perPage);

        $data = [
            'page'      => $page,
            'per_page'  => $perPage,
            'total'     => $total,
            'data'      => $paginator->getQuery()->getArrayResult()
        ];

        if (empty($groupName)) {
            $responseData = $serializer->serialize($data, 'json', SerializationContext::create()->setSerializeNull(true));
        } else {
            $responseData = $serializer->serialize($data, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups([$groupName]));
        }

        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    /**
     * @param string $message
     * @param int $httpStatus
     * @param array $data
     * @param array $groups
     * @param array $headers
     * @return JsonResponse
     */
    protected function respondSuccess($httpStatus = Response::HTTP_OK, $message = '', $data = [], $groups = [], $headers = [])
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');

        $responseData = [];

        if (!empty($message)) {
            $responseData['message'] = $message;
        } elseif (isset(ResponseCode::$titles[$httpStatus])) {
            $responseData['code']    = $httpStatus;
            $responseData['message'] = ResponseCode::$titles[$httpStatus]['message'];
            $httpStatus              = ResponseCode::$titles[$httpStatus]['httpCode'];
        }

        if (!empty($data)) {
            $responseData = $data;
        }

        if (empty($groups)) {
            $responseData = $serializer->serialize($responseData, 'json', SerializationContext::create()->setSerializeNull(true));
        } else {
            $responseData = $serializer->serialize($responseData, 'json', SerializationContext::create()->setSerializeNull(true)->setGroups($groups));
        }

        return new JsonResponse($responseData, $httpStatus, $headers, true);
    }

    /**
     * @param Request $request
     * @param $data
     * @param $fields
     * @return PdfResponse
     * @throws \Exception
     */
    protected function respondPdf(Request $request, $data, $fields)
    {
        return $this->respondFile(
            '@api_grid/grid.html.twig',
            'output',
            'pdf',
            [
                'title'  => 'Title',
                'fields' => $fields,
                'data'   => $data
            ]
        );
    }

    /**
     * @param $template
     * @param string $actualName
     * @param string $format
     * @param array $params
     * @return PdfResponse|Response
     * @throws \Exception
     */
    protected function respondFile($template, $actualName, $format = 'pdf', array $params = [])
    {
        $options = [];

        if (property_exists($params['data'], 'options')) {
            $options = $params['data']->getOptions();
        }

        $html = $this->renderView($template, $params);

        if ($format == 'pdf') {
            return new PdfResponse($this->pdf->getOutputFromHtml($html, $options), $actualName .'.pdf');
        } elseif($format == 'csv') {
            return new Response($html, Response::HTTP_OK, [
                'Content-Type'              => 'text/csv',
                'Content-Disposition'       => 'attachment; filename="'. $actualName .'.csv"',
                'Content-Transfer-Encoding' => 'binary',
                'Pragma'                    => 'no-cache',
                'Expires'                   => '0',
            ]);
        }

        throw new \Exception('Support only pdf and csv formats');
    }

    /**
     * @param Request $request
     * @param string $group
     * @param string $alias
     * @param ReportService $reportService
     * @return PdfResponse
     * @throws \Exception
     */
    protected function respondReport(Request $request, string $group, string $alias, ReportService $reportService)
    {
        $report = $reportService->report($request, $group, $alias);

        if ($request->get('template')) {
            $file = '@api_report/'. $group . '/' . $request->get('template') .'.' . $request->get('format') . '.twig';
        } else {
            $file = '@api_report/'. $group . '/' . $alias .'.' . $request->get('format') . '.twig';
        }

        return $this->respondFile(
            $file,
            $group . '-' . $alias,
            $request->get('format'),
            ['data' => $report]
        );
    }

    /**
     * @param string $entityName
     * @param string $groupName
     * @return JsonResponse
     * @throws \ReflectionException
     */
    protected function getOptionsByGroupName(string $entityName, string $groupName)
    {
        $options = $this->getGrid($entityName)->getGroupOptions($groupName);

        if (!$options) {
            throw new GridOptionsNotFoundException();
        }

        if (!$this->grantService->hasCurrentUserEntityGrant(Space::class, Grant::$LEVEL_VIEW)) {
            $options = array_values(array_filter($options, function ($value) {
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
     * @throws \ReflectionException
     */
    protected function getQueryBuilder(Request $request, string $entityName, string $groupName)
    {
        return $this->getGrid($entityName)
             ->setEntityManager($this->em)
             ->renderByGroup($request->query->all(), $groupName)
             ->getQueryBuilder();
    }

    /**
     * @param $entityName
     * @return null|object|Grid
     * @throws \ReflectionException
     */
    private function getGrid($entityName)
    {
        /**
         * @var Grid $annotation
         */
        $reflectionClass = new \ReflectionClass($entityName);

        return $this->reader->getClassAnnotation($reflectionClass, Grid::class);
    }

    /**
     * @param string $title
     * @param resource $resource
     * @return Response
     */
    protected function respondResource($title, $resource)
    {
        if(!empty($resource)) {
            $data = stream_get_contents($resource);
            $mime = MimeUtil::getMime($data);

            return new Response($data, Response::HTTP_OK, [
                'Content-Type' => $mime,
                'Content-Length' => strlen($data),
                'Content-Disposition' => 'attachment; filename="' . StringUtil::slugify($title) . '.' . MimeUtil::mime2ext($mime) . '"'
            ]);
        }

        throw new FileNotFoundException("");
    }
}
