<?php

namespace App\Model\Report;

use App\Entity\Resident;

class BloodPressureCharting extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * @return mixed
     */
    public function getResidents()
    {
        return $this->residents;
    }

    /**
     * @param array $residents
     * @return bool
     */
    public function setResidents($residents)
    {
        $residentsByType = [];

        foreach ($residents as $resident) {
            if (!isset($residentsByType[$resident['type']][$resident['typeId']]['name'])) {
                $residentsByType[$resident['type']][$resident['typeId']]['name'] = $resident['name'];
            }

            $residentsByType[$resident['type']][$resident['typeId']]['data'][] = $resident;
        }

        $this->residents = $residentsByType;
    }
}

