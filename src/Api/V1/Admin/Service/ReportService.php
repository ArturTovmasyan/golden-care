<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\Exception\IncorrectReportParameterException;
use App\Api\V1\Common\Service\Exception\ReportFormatNotFoundException;
use App\Api\V1\Common\Service\Exception\ReportMisconfigurationException;
use App\Api\V1\Common\Service\Exception\ReportNotFoundException;
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

    /**
     * ReportService constructor.
     * @param ContainerInterface $container
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @param Reader $reader
     * @param Mailer $mailer
     * @param Security $security
     */
    public function __construct(
        ContainerInterface $container,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder,
        Reader $reader,
        Mailer $mailer,
        Security $security
    )
    {
        $this->container = $container;
        $this->em = $em;
        $this->validator = $validator;
        $this->encoder = $encoder;
        $this->reader = $reader;
        $this->mailer = $mailer;
        $this->security = $security;
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

        if (is_null($report)) {
            throw new ReportNotFoundException();
        }

        if (!in_array($request->get('format'), $report['formats'])) {
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
            $this->reader
        );

        // TODO: add call to function with checked parameters

        return $service->$action($request);
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
        $date_from = $request->get('date_from') ?? null;
        $date_to = $request->get('date_to') ?? null;

        $parameter['group'] = array_key_exists('group', $parameters);
        $parameter['group_id'] = $parameter['group'];
        $parameter['group_all'] = $parameter['group'] ? $parameters['group']['select_all'] : false;

        $parameter['resident_id'] = array_key_exists('resident', $parameters);
        $parameter['resident_all'] = $parameter['resident_id'] ? $parameters['resident']['select_all'] : false;

        $parameter['date'] = array_key_exists('date', $parameters);
        $parameter['date_to'] = array_key_exists('date_to', $parameters);
        $parameter['date_from'] = array_key_exists('date_from', $parameters);

        if ($parameter['resident_id'] == true && $parameter['group'] == false) {
            $this->checkResidentParameters($parameter, $residentId, $residentAll, $request_param_map);
        }

        if ($parameter['resident_id'] == false && $parameter['group'] == true) {
            $this->checkGroupParameters($parameter, $group, $groupId, $groupAll, $request_param_map);
        }

        if ($parameter['date'] == true && $date == null) {
            throw new IncorrectReportParameterException([$request_param_map['date']]);
        }
        if ($parameter['date_from'] == true && $date_from == null) {
            throw new IncorrectReportParameterException([$request_param_map['date_from']]);
        }
        if ($parameter['date_to'] == true && $date_to == null) {
            throw new IncorrectReportParameterException([$request_param_map['date_to']]);
        }

    }

    private function checkResidentParameters($parameter, $residentId, $residentAll, $request_param_map)
    {
        if ($residentId == null && $residentAll == null) {
            throw new IncorrectReportParameterException([$request_param_map['resident_id']]);
        }

        if ($residentId == null) {
            if ($parameter['resident_all'] == true && $residentAll == null) {
                throw new IncorrectReportParameterException([$request_param_map['resident_all']]);
            }
            if ($parameter['resident_all'] == false && $residentAll != null) {
                throw new IncorrectReportParameterException([$request_param_map['resident_all']]);
            }
        }
    }

    private function checkGroupParameters($parameter, $group, $groupId, $groupAll, $request_param_map)
    {
        if ($group == null) {
            throw new IncorrectReportParameterException([$request_param_map['group']]);
        }

        if ($groupId == null && $groupAll == null) {
            throw new IncorrectReportParameterException([$request_param_map['group_id']]);
        }

        if ($groupId == null) {
            if ($parameter['group_all'] == true && $groupAll == null) {
                throw new IncorrectReportParameterException([$request_param_map['group_all']]);
            }
            if ($parameter['group_all'] == false && $groupAll != null) {
                throw new IncorrectReportParameterException([$request_param_map['group_all']]);
            }
        }
    }
}
