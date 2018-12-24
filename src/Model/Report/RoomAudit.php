<?php

namespace App\Model\Report;

class RoomAudit extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * RoomAudit constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this
            ->addOption('footer-spacing', 4)
            ->addOption('footer-center', ' _________________________________________________________________________________________________________________________________________
NP = NEW POLISH      F = FILE\TRIM      R = REFUSED      O = OTHER (please specify)
                                                    ');
    }

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

            $residentsByType[$resident['type']][$resident['typeId']]['data'][] = $resident;
        }

        $this->residents = $residentsByType;
    }
}

