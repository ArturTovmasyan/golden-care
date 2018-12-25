<?php

namespace App\Model\Report;

use App\Model\Phone;

class ResidentDetailedRoster extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * @var array
     */
    private $responsiblePersons = [];

    /**
     * ResidentDetailedRoster constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addOption('orientation', self::ORIENTATION_LANDSCAPE);
    }

    /**
     * @return mixed
     */
    public function getResidents()
    {
        return $this->residents;
    }

    /**
     * @return mixed
     */
    public function getResponsiblePersons()
    {
        return $this->responsiblePersons;
    }

    /**
     * @param $residents
     */
    public function setResidents($residents)
    {
        $residentsByType = [];

        foreach ($residents as $resident) {
            if (!isset($residentsByType[$resident['type']][$resident['typeId']]['name'])) {
                $residentsByType[$resident['type']][$resident['typeId']]['name'] = $resident['typeName'];
            }

            $residentsByType[$resident['type']][$resident['typeId']]['data'][] = $resident;
        }

        $this->residents = $residentsByType;
    }

    /**
     * @param $responsiblePersons
     */
    public function setResponsiblePersons($responsiblePersons)
    {
        $responsiblePersonsByResidentId = [];

        foreach ($responsiblePersons as $responsiblePerson) {
            if (!isset($responsiblePersonsByResidentId[$responsiblePerson['residentId']][$responsiblePerson['id']])) {
                $responsiblePersonsByResidentId[$responsiblePerson['residentId']][$responsiblePerson['id']] = $responsiblePerson;
            }

            $responsiblePersonsByResidentId[$responsiblePerson['residentId']][$responsiblePerson['id']]['phones'][] = [
                'type'      => $responsiblePerson['phoneType'],
                'typeName'  => Phone::$typeNames[$responsiblePerson['phoneType']],
                'extension' => $responsiblePerson['phoneExtension'],
                'number'    => $responsiblePerson['phoneNumber'],
            ];
        }

        $this->responsiblePersons = $responsiblePersonsByResidentId;
    }
}

