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
}

