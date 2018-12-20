<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\Exception\ReportFormatNotFoundException;
use App\Api\V1\Common\Service\Exception\ReportMisconfigurationException;
use App\Api\V1\Common\Service\Exception\ReportNotFoundException;
use App\Model\Report;
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

        return $this->array_remove_keys($config_filtered, ['service', 'template']);
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

        if(!$rc->isPublic()) {
            throw new ReportMisconfigurationException();
        }

        $service = new $service (
            $this->em,
            $this->encoder,
            $this->mailer,
            $this->validator,
            $this->security,
            $this->reader
        );

        return $service->$action($request);
    }

    private function array_remove_keys($array, $keys = array())
    {
        // If $keys is a comma-separated list, convert to an array.
        if (is_string($keys)) {
            $keys = explode(',', $keys);
        }

        // If array is empty or not an array at all, don't bother
        // doing anything else.
        if (empty($array) || (!is_array($array))) {
            return $array;
        }

        // array_diff_key() expected an associative array.
        $assocKeys = array();
        foreach ($keys as $key) {
            $assocKeys[$key] = true;
        }

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->array_remove_keys($array[$key], $keys);
            }
        }

        return array_diff_key($array, $assocKeys);
    }
}
