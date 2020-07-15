<?php

namespace App\Model\Report;

class UserLogActivity extends Base
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): ?array
    {
        return $this->data;
    }
}

