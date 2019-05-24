<?php

namespace App\Model\Report\Lead;

use App\Model\Report\Base;

class ActivityList extends Base
{
    /**
     * @var array
     */
    private $activities = [];

    /**
     * @param $activities
     */
    public function setActivities($activities): void
    {
        $this->activities = $activities;
    }

    /**
     * @return array
     */
    public function getActivities(): ?array
    {
        return $this->activities;
    }
}

