<?php

namespace App\Model\Report\Lead;

use App\Model\Report\Base;

class LeadList extends Base
{
    /**
     * @var array
     */
    private $leads = [];

    /**
     * @param $leads
     */
    public function setLeads($leads): void
    {
        $this->leads = $leads;
    }

    /**
     * @return array
     */
    public function getLeads(): ?array
    {
        return $this->leads;
    }
}

