<?php

namespace App\Model\Report;

class Manicure extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * @var array
     */
    private $options = [
        'footer-spacing' => 4,
        'footer-center' => ' _________________________________________________________________________________________________________________________________________
NP = NEW POLISH      F = FILE\TRIM      R = REFUSED      O = OTHER (please specify)
                                                    '
    ];

    /**
     * @return mixed
     */
    public function getResidents()
    {
        return $this->residents;
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
        $residentsByType = [];

        foreach ($residents as $resident) {
            if (!isset($residentsByType[$resident['type']][$resident['typeId']]['name'])) {
                $residentsByType[$resident['type']][$resident['typeId']]['name'] = $resident['name'];
            }

            $residentsByType[$resident['type']][$resident['typeId']]['data'][] = $resident;
        }

        $this->residents = $residentsByType;
    }
}

