<?php

namespace App\Api\V1\Common\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class GrantService
 * @package App\Api\V1\Service
 */
class GrantService
{
    private static $GRANT_CONFIG_PATH = '/../src/Api/V1/Common/Resources/config/grants.yaml';

    /** @var array */
    private $config;

    /**
     * GrantService constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->config = Yaml::parseFile($container->get('kernel')->getRootDir() . self::$GRANT_CONFIG_PATH);
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getGrants($values, $tree = null, $parent_key = '', $parent_fields = [])
    {
        $grid_config = [];

        if ($tree === null) {
            $tree = $this->config;
        }

        foreach ($tree as $key => $grant_node) {
            if (array_key_exists('fields', $grant_node)) {
                $fields = $grant_node['fields'];
            } else {
                $fields = $parent_fields;
            }

            $key_path = $parent_key != '' ? $parent_key . '-' . $key : $key;
            $children = array_key_exists('children', $grant_node) ? $this->getGrants($values, $grant_node['children'], $key_path, $fields) : [];

            $node = [
                'key' => $key_path,
                'title' => $grant_node['title'] ?? ''
            ];

            if (count($children) > 0) {
                $node['children'] = $children;
            } else {
                if (array_key_exists($key_path, $values)) {
                    if (in_array('enabled', $fields)) {
                        $node['enabled'] = $values[$key_path]['enabled'] ?? false;
                    }
                    if (in_array('level', $fields)) {
                        $node['level'] = $values[$key_path]['level'] ?? 0;
                    }
                    if (in_array('identity', $fields)) {
                        $node['identity'] = $values[$key_path]['identity'] ?? 0;
                    }
                } else {
                    if (in_array('enabled', $fields)) {
                        $node['enabled'] = false;
                    }
                    if (in_array('level', $fields)) {
                        $node['level'] = 0;
                    }
                    if (in_array('identity', $fields)) {
                        $node['identity'] = 0;
                    }
                }
            }

            $grid_config[] = $node;
        }

        return $grid_config;
    }
}
