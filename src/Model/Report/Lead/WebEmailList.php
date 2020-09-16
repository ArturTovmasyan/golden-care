<?php

namespace App\Model\Report\Lead;

use App\Model\Report\Base;

class WebEmailList extends Base
{
    /**
     * @var array
     */
    private $webEmails = [];

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param $webEmails
     */
    public function setWebEmails($webEmails): void
    {
        $this->webEmails = $webEmails;
    }

    /**
     * @return array
     */
    public function getWebEmails(): ?array
    {
        return $this->webEmails;
    }

    /**
     * @return array
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }
}

