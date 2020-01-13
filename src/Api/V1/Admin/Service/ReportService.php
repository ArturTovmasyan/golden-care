<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\Exception\IncorrectReportParameterException;
use App\Api\V1\Common\Service\Exception\ReportFormatNotFoundException;
use App\Api\V1\Common\Service\Exception\ReportMisconfigurationException;
use App\Api\V1\Common\Service\Exception\ReportNotFoundException;
use App\Api\V1\Common\Service\GrantService;
use App\Api\V1\Common\Service\S3Service;
use App\Util\ArrayUtil;
use App\Util\Mailer;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ReportService
 * @package App\Api\V1\Admin\Service
 */
class ReportService
{
    private static $REPORT_CONFIG_PATH = '/src/Api/V1/Common/Resources/config/reports.yaml';

    /** @var array */
    private $config;

    /** @var ContainerInterface */
    private $container;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var UserPasswordEncoderInterface */
    protected $encoder;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var Mailer */
    protected $mailer;

    /** @var Security */
    protected $security;

    /** @var Reader */
    protected $reader;

    /** @var GrantService */
    protected $grantService;

    /** @var  S3Service */
    protected $s3Service;

    /**
     * ReportService constructor.
     * @param ContainerInterface $container
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @param Reader $reader
     * @param Mailer $mailer
     * @param Security $security
     * @param GrantService $grantService
     * @param S3Service $s3Service
     */
    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder,
        Reader $reader,
        Mailer $mailer,
        Security $security,
        GrantService $grantService,
        S3Service $s3Service
    )
    {
        $this->container = $container;
        $this->em = $em;
        $this->validator = $validator;
        $this->encoder = $encoder;
        $this->reader = $reader;
        $this->mailer = $mailer;
        $this->security = $security;
        $this->grantService = $grantService;
        $this->s3Service = $s3Service;
        $this->config = Yaml::parseFile($this->container->get('kernel')->getProjectDir() . self::$REPORT_CONFIG_PATH);
    }

    public function list(): ?array
    {
        $config_filtered = $this->config;

        foreach ($config_filtered as $group => $config) {
            foreach ($config['reports'] as $alias => $report) {
                $grant = sprintf('report-%s-%s', $group, $alias);
                if ($this->grantService->hasCurrentUserGrant($grant) === false) {
                    unset($config_filtered[$group]['reports'][$alias]);
                }
            }

            if (\count($config_filtered[$group]['reports']) === 0) {
                unset($config_filtered[$group]);
            }
        }

        return ArrayUtil::remove_keys($config_filtered, ['service', 'template']);
    }

    /**
     * @param Request $request
     * @param string $group
     * @param string $alias
     * @return mixed
     */
    public function report(Request $request, string $group, string $alias)
    {
        if (!array_key_exists($group, $this->config) || !array_key_exists($alias, $this->config[$group]['reports'])) {
            throw new ReportNotFoundException();
        }

        $report = $this->config[$group]['reports'][$alias];

        if ($report === null) {
            throw new ReportNotFoundException();
        }

        if (!\in_array($request->get('format'), $report['formats'], false)) {
            throw new ReportFormatNotFoundException();
        }

        $grant = sprintf('report-%s-%s', $group, $alias);
        if ($this->grantService->hasCurrentUserGrant($grant) === false) {
            throw new AccessDeniedHttpException();
        }

        if (!empty($report['template'])) {
            $request->request->add(['template' => $report['template']]);
        }

        [$service, $action] = explode('::', $report['service']);

        if (empty($service) || empty($action)) {
            throw new ReportMisconfigurationException();
        }

        $rc = new ReflectionMethod($service, $action);

        if (!$rc->isPublic()) {
            throw new ReportMisconfigurationException();
        }

        $this->checkParameters($request, $group, $alias);

        $service = new $service (
            $this->em,
            $this->encoder,
            $this->mailer,
            $this->validator,
            $this->security,
            $this->reader,
            $this->grantService,
            $this->container,
            $this->s3Service
        );

        $request_group = $request->get('type') ? (int)$request->get('type') : null;
        $request_groupAll = $request->get('type_all') ? (bool)$request->get('type_all') : null;
        $request_groupId = $request->get('type_id') ? (int)$request->get('type_id') : null;

        $request_residentAll = $request->get('resident_all') ? (bool)$request->get('resident_all') : null;
        $request_residentId = $request->get('resident_id') ? (int)$request->get('resident_id') : null;

        $request_date = $request->get('date') ?? null;
        $request_dateFrom = $request->get('date_from') ?? null;
        $request_dateTo = $request->get('date_to') ?? null;

        $request_assessmentId = $request->get('assessment_id') ? (int)$request->get('assessment_id') : null;
        $request_assessmentFormId = $request->get('assessment_form_id') ? (int)$request->get('assessment_form_id') : null;

        $discontinued = $request->get('discontinued') ? (bool)$request->get('discontinued') : false;

        return $service->$action(
            $request_group,
            $request_groupAll,
            $request_groupId,
            $request_residentAll,
            $request_residentId,
            $request_date,
            $request_dateFrom,
            $request_dateTo,
            $request_assessmentId,
            $request_assessmentFormId,
            $discontinued
        );
    }

    private function checkParameters(Request $request, string $group, string $alias): void
    {
        $request_param_map = [
            'assessment_id' => 'assessment_id',

            'group' => 'type',
            'group_all' => 'type_all',
            'group_id' => 'type_id',

            'resident_id' => 'resident_id',
            'resident_all' => 'resident_all',

            'date' => 'date',
            'date_from' => 'date_from',
            'date_to' => 'date_to'
        ];

        $parameters = $this->config[$group]['reports'][$alias]['parameters'];

        $assessmentId = $request->get('assessment_id') ?? null;
        $assessmentFormId = $request->get('assessment_form_id') ?? null;

        $group = $request->get('type') ?? null;
        $groupAll = $request->get('type_all') ?? null;
        $groupId = $request->get('type_id') ?? null;

        $residentAll = $request->get('resident_all') ?? null;
        $residentId = $request->get('resident_id') ?? null;

        $date = $request->get('date') ?? null;
        $dateFrom = $request->get('date_from') ?? null;
        $dateTo = $request->get('date_to') ?? null;

        $configParameter = [];
        $configParameter['group'] = array_key_exists('group', $parameters);
        $configParameter['group_id'] = $configParameter['group'];
        $configParameter['group_all'] = $configParameter['group'] ? $parameters['group']['select_all'] : false;

        $configParameter['resident'] = array_key_exists('resident', $parameters);
        $configParameter['resident_all'] = $configParameter['resident'] ? $parameters['resident']['select_all'] : false;

        $configParameter['date'] = array_key_exists('date', $parameters);
        $configParameter['date_to'] = array_key_exists('date_to', $parameters);
        $configParameter['date_from'] = array_key_exists('date_from', $parameters);

        if ($date === null && $configParameter['date'] === true) {
            throw new IncorrectReportParameterException([$request_param_map['date']]);
        }

        if ($dateFrom === null && $configParameter['date_from'] === true) {
            throw new IncorrectReportParameterException([$request_param_map['date_from']]);
        }

        if ($dateTo === null && $configParameter['date_to'] === true) {
            throw new IncorrectReportParameterException([$request_param_map['date_to']]);
        }

        // TODO: review temp solution
        if ($assessmentId !== null || $assessmentFormId !== null) {
            return;
        }

        if ($group === null) {
            throw new IncorrectReportParameterException([$request_param_map['group']]);
        }

        if ($configParameter['resident'] === true && $configParameter['group'] === false) {
            if (($residentId === null && $residentAll === null) || ($residentId !== null && $residentAll !== null)) {
                throw new IncorrectReportParameterException([
                    $request_param_map['resident_id'],
                    $request_param_map['resident_all']
                ]);
            }

            if ($residentId === null) {
                if ($residentAll === null && $configParameter['resident_all'] === true) {
                    throw new IncorrectReportParameterException([$request_param_map['resident_all']]);
                }
                if ($residentAll !== null && $configParameter['resident_all'] === false) {
                    throw new IncorrectReportParameterException([$request_param_map['resident_all']]);
                }
            }
        }

        if ($configParameter['resident'] === false && $configParameter['group'] === true) {
            if (($groupId === null && $groupAll === null) || ($groupId !== null && $groupAll !== null)) {
                throw new IncorrectReportParameterException([
                    $request_param_map['group_id'],
                    $request_param_map['group_all']
                ]);
            }

            if ($groupId === null) {
                if ($groupAll === null && $configParameter['group_all'] === true) {
                    throw new IncorrectReportParameterException([$request_param_map['group_all']]);
                }
                if ($groupAll !== null && $configParameter['group_all'] === false) {
                    throw new IncorrectReportParameterException([$request_param_map['group_all']]);
                }
            }
        }

        if ($configParameter['resident'] === true && $configParameter['group'] === true) {
            throw new IncorrectReportParameterException([
                $request_param_map['group_id'],
                $request_param_map['group_all'],
                $request_param_map['resident_id'],
                $request_param_map['resident_all']
            ]);
        }
    }

    public function getGroupReportGrants(): ?array
    {
        $group_grants = [];

        foreach ($this->config as $group => $config) {
            if ($config['show_in_group_list'] === true) {
                foreach ($config['reports'] as $alias => $report) {
                    $grant = sprintf('report-%s-%s', $group, $alias);
                    if ($report['show_in_group_list'] === true && $this->grantService->hasCurrentUserGrant($grant) === true) {
                        $group_grants[] = $grant;
                    }
                }
            }
        }

        return $group_grants;
    }

    public function addGroupReportPermission(array &$permissions): void
    {
        $group_grants = $this->getGroupReportGrants();

        $report_test = array_filter($permissions, function ($value, $key) use ($group_grants) {
            return \in_array($key, $group_grants, false) && $value['enabled'] === true;
        }, ARRAY_FILTER_USE_BOTH);

        if (\count($report_test) > 0) {
            $permissions['report-group'] = ['enabled' => true];
        }
    }
}
