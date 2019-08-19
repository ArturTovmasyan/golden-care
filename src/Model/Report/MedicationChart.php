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
    private $residentPhysicians = [];

    /**
     * @var array
     */
    private $physicianPhones = [];

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

    /**
     * @return array
     */
    public function getMedications(): ?array
    {
        return $this->medications;
    }

    /**
     * @return array
     */
    public function getAllergens(): ?array
    {
        return $this->allergens;
    }

    /**
     * @param $medications
     */
    public function setMedications($medications): void
    {
        foreach ($medications as $medication) {
            $this->medications[$medication['residentId']][] = $medication;
        }
    }

    /**
     * @param $allergens
     */
    public function setAllergens($allergens): void
    {
        foreach ($allergens as $allergen) {
            $this->allergens[$allergen['residentId']][] = $allergen;
        }
    }

    /**
     * @return array
     */
    public function getResidentPhysicians(): ?array
    {
        return $this->residentPhysicians;
    }

    /**
     * @param $physicians
     */
    public function setResidentPhysicians($physicians): void
    {
        foreach ($physicians as $physician) {
            $this->residentPhysicians[$physician['residentId']][] = $physician;
        }
    }

    /**
     * @param $physicianPhones
     */
    public function setPhysicianPhones($physicianPhones): void
    {
        $this->physicianPhones = $physicianPhones;
    }

    /**
     * @return array
     */
    public function getPhysicianPhones(): ?array
    {
        return $this->physicianPhones;
    }
}

