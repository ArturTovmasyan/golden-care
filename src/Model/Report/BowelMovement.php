<?php

namespace App\Model\Report;

class BowelMovement extends Base
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
     * @param $residents
     */
    public function setResidents($residents)
    {
        $residentsByTypeAndGroup = [];

        foreach ($residents as $resident) {
            if (!isset($residentsByTypeAndGroup[$resident['type']][$resident['typeId']][$resident['careGroup']]['name'])) {
                $residentsByTypeAndGroup[$resident['type']][$resident['typeId']][$resident['careGroup']]['name']      = $resident['name'];
                $residentsByTypeAndGroup[$resident['type']][$resident['typeId']][$resident['careGroup']]['groupName'] = $resident['careGroup'];
            }

            $residentsByTypeAndGroup[$resident['type']][$resident['typeId']][$resident['careGroup']]['data'][] = $resident;
        }

        $this->residents = $residentsByTypeAndGroup;
    }
}

