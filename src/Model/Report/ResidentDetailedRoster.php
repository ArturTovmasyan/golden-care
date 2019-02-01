<?php

namespace App\Model\Report;

class ResidentDetailedRoster extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * @var array
     */
    private $responsiblePersonPhones = [];

    /**
     * ResidentDetailedRoster constructor.
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
     * @param $responsiblePersons
     */
    public function setResponsiblePersons($responsiblePersons): void
    {
        foreach ($responsiblePersons as $responsiblePerson) {
            $this->residents[$responsiblePerson['residentId']]['responsiblePersons'][] = $responsiblePerson;
        }
    }

    /**
     * @param $physicians
     */
    public function setPhysicians($physicians): void
    {
        foreach ($physicians as $physician) {
            $this->residents[$physician['residentId']]['physicians'][] = $physician;
        }
    }

    /**
     * @param $responsiblePersonPhones
     */
    public function setResponsiblePersonPhones($responsiblePersonPhones): void
    {
        $this->responsiblePersonPhones = $responsiblePersonPhones;
    }

    /**
     * @return array
     */
    public function getResponsiblePersonPhones(): ?array
    {
        return $this->responsiblePersonPhones;
    }
}

