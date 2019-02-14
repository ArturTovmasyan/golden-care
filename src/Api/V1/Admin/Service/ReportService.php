<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\Exception\IncorrectReportParameterException;
use App\Api\V1\Common\Service\Exception\ReportFormatNotFoundException;
use App\Api\V1\Common\Service\Exception\ReportMisconfigurationException;
use App\Api\V1\Common\Service\Exception\ReportNotFoundException;
use App\Api\V1\Common\Service\GrantService;
use App\Model\Report;
use App\Util\ArrayUtil;
use App\Util\Mailer;
use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
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
    private static $REPORT_CONFIG_PATH = '/../src/Api/V1/Common/Resources/config/reports.yaml';

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
     */
    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder,
        Reader $reader,
        Mailer $mailer,
        Security $security,
        GrantService $grantService
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
        $this->config = Yaml::parseFile($this->container->get('kernel')->getRootDir() . self::$REPORT_CONFIG_PATH);
    }

    public function list()
    {
        $config_filtered = $this->config;

        return ArrayUtil::remove_keys($config_filtered, ['service', 'template']);
    }

    /**
     * @param Request $request
     * @param string $group
     * @param string $alias
     * @return Report\Base
     * @throws \Exception
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
            $this->grantService
        );

        $request_group = $request->get('type') ? (int) $request->get('type') : null;
        $request_groupAll = $request->get('type_all') ? (bool)$request->get('type_all') : null;
        $request_groupId = $request->get('type_id') ? (int) $request->get('type_id') : null;

        $request_residentAll = $request->get('resident_all') ? (bool)$request->get('resident_all') : null;
        $request_residentId = $request->get('resident_id') ? (int) $request->get('resident_id') : null;

        $request_date = $request->get('date') ?? null;
        $request_dateFrom = $request->get('date_from') ?? null;
        $request_dateTo = $request->get('date_to') ?? null;


        return $service->$action(
            $request_group,
            $request_groupAll,
            $request_groupId,
            $request_residentAll,
            $request_residentId,
            $request_date,
            $request_dateFrom,
            $request_dateTo
        );
    }

    private function checkParameters(Request $request, string $group, string $alias)
    {
        $request_param_map = [
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

        if ($date === null && $configParameter['date'] === true) {
            throw new IncorrectReportParameterException([$request_param_map['date']]);
        }

        if ($dateFrom === null && $configParameter['date_from'] === true) {
            throw new IncorrectReportParameterException([$request_param_map['date_from']]);
        }

        if ($dateTo === null && $configParameter['date_to'] === true) {
            throw new IncorrectReportParameterException([$request_param_map['date_to']]);
        }
    }
}
