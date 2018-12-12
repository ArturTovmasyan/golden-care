<?php

namespace App\Model\Report;


use App\Entity\Facility;
use App\Entity\Resident;

class ResidentBirthdayList extends Base
{
    /**
     * @var array
     */
    private $types = [];

    /**
     * @return mixed
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param Resident[] $residents
     * @return bool
     */
    public function setResidents($residents)
    {
        if (empty($residents)) {
            return false;
        }

        $now = new \DateTime();

        /**
         * @var Resident $resident
         */
        foreach ($residents as $key => $resident) {
            if ($resident->getType() == \App\Model\Resident::TYPE_FACILITY) {
                $facility = $resident->getResidentFacilityOption()->getFacilityRoom()->getFacility();
                $name   = $facility->getName();
            } elseif ($resident->getType() == \App\Model\Resident::TYPE_APARTMENT) {
                $apartment = $resident->getResidentApartmentOption()->getApartmentRoom()->getApartment();
                $name   = $apartment->getName();
            } else {
                $region = $resident->getResidentRegionOption()->getRegion();
                $name   = $region->getName();
            }

            $this->types[$key]['name'] = $name;

            if (empty($this->types[$key]['dates'])) {
                $this->types[$key]['dates'] = $this->locateMonths();
            }

            $birthday = $resident->getBirthday();

            $this->types[$key]['dates'][$birthday->format('n')]['birthdays'][] =
                [
                    'name' => $resident->getFirstName() . ' ' . $resident->getLastName(),
                    'age'  => $now->diff($birthday)->y + 1,
                    'day'  => $birthday->format('m/d/Y')
                ];
        }
    }

    /**
     * @return array
     */
    private function locateMonths()
    {
        $months = [];

        for ($i = 1; $i < 13; $i++) {
            $months[$i]['name'] = date('F', mktime(0, 0, 0, $i, 10));
        }

        return $months;
    }
}

