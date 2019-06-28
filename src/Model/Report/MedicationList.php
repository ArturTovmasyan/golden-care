<?php

namespace App\Model\Report;

class MedicationList extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * @var boolean
     */
    private $discontinued;

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
     * @param $medications
     */
    public function setMedications($medications): void
    {
        foreach ($medications as $medication) {
            if ($medication['medicationDiscont']) {
                $this->residents[$medication['residentId']]['medications']['discontinued'][] = $medication;
            } else {
                $this->residents[$medication['residentId']]['medications']['active'][] = $medication;
            }
        }
    }

    /**
     * @return bool
     */
    public function isDiscontinued(): bool
    {
        return $this->discontinued;
    }

    /**
     * @param bool $discontinued
     */
    public function setDiscontinued(bool $discontinued): void
    {
        $this->discontinued = $discontinued;
    }
}

