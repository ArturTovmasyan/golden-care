<?php

namespace App\Model\Report;

class BloodPressureChart extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * @param $residents
     */
    public function setResidents($residents): void
    {
        $this->residents = $residents;
    }

    /**
     * @return array
     */
    public function getResidents(): ?array
    {
        return $this->residents;
    }
}
