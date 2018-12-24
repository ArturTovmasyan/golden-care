<?php

namespace App\Model\Report;

class MedicationChart extends Base
{
    /**
     * @var array
     */
    private $medications = [];

    /**
     * @var array
     */
    private $allergens = [];

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
     * @return array
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
        $this->residents = $residents;
    }

    /**
     * @return array
     */
    public function getMedications()
    {
        return $this->medications;
    }

    /**
     * @return array
     */
    public function getAllergens()
    {
        return $this->allergens;
    }

    /**
     * @param $medications
     */
    public function setMedications($medications)
    {
        foreach ($medications as $medication) {
            $this->medications[$medication['residentId']][] = $medication;
        }
    }

    /**
     * @param $allergens
     */
    public function setAllergens($allergens)
    {
        foreach ($allergens as $allergen) {
            $this->allergens[$allergen['residentId']][] = $allergen;
        }
    }
}

