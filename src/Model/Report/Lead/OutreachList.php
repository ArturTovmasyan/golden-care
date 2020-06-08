<?php

namespace App\Model\Report\Lead;

use App\Model\Report\Base;

class OutreachList extends Base
{
    /**
     * @var array
     */
    private $outreaches = [];

    /**
     * @param $outreaches
     */
    public function setOutreaches($outreaches): void
    {
        $this->outreaches = $outreaches;
    }

    /**
     * @return array
     */
    public function getOutreaches(): ?array
    {
        return $this->outreaches;
    }
}

