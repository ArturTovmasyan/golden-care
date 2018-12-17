<?php

namespace App\Model\Report;

class DietaryRestriction extends Base
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
        $residentsByType = [];

        foreach ($residents as $resident) {
            if (!isset($residentsByType[$resident['type']][$resident['typeId']]['name'])) {
                $residentsByType[$resident['type']][$resident['typeId']]['name'] = $resident['name'];
            }

            if (!isset($residentsByType[$resident['type']][$resident['typeId']]['data'][$resident['id']])) {
                $residentsByType[$resident['type']][$resident['typeId']]['data'][$resident['id']] = $resident;
            }

            $residentsByType[$resident['type']][$resident['typeId']]['data'][$resident['id']]['diets'][] = [
                'color'       => $resident['dietColor'],
                'title'       => $resident['dietTitle'],
                'description' => $resident['dietDescription']
            ];
        }

        $this->residents = $residentsByType;
    }
}

