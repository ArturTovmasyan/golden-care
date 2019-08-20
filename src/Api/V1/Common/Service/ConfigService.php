<?php

namespace App\Api\V1\Common\Service;

/**
 * Class ConfigService
 * @package App\Api\V1\Service
 */
class ConfigService
{
    private $config = [];

    public function __construct()
    {
        $this->config = $this->download();
    }

    public function download()
    {
        $data = \file_get_contents(
//            'http://seniorcare-mc.local/backend/api/5766d45bdba1152105abfd9662e55140/config',
            'https://console.seniorcaresw.com/backend/api/5766d45bdba1152105abfd9662e55140/config'
        );

        if ($data !== false) {
            $data = \json_decode($data, true);

            if ($data !== null) {
                return $data;
            }
        }

        return [];
    }

    public function get(string $key)
    {
        return \array_key_exists($key, $this->config) ? $this->config[$key] : null;
    }
}
