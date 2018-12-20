<?php

namespace App\Model\Report;

class MedicationList extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * @var array
     */
    private $options = [];

    /**
     * MealMonitor constructor.
     */
    public function __construct()
    {
        $this->options = [
            'orientation'  => 'Landscape'
        ];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $residents
     */
    public function setResidents($residents)
    {
        $this->residents = $residents;
    }

    /**
     * @return array
     */
    public function getResidents()
    {
        return $this->residents;
    }

    /**
     * @param $medications
     */
    public function setMedications($medications)
    {
        foreach ($medications as $medication) {
            $this->residents[$medication['residentId']]['medications'][] = $medication;
        }
    }
}

