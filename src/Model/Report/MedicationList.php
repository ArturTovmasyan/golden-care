<?php

namespace App\Model\Report;

class MedicationList extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * MealMonitor constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addOption('orientation', self::ORIENTATION_LANDSCAPE);
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

