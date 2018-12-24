<?php

namespace App\Model\Report;

class NightActivity extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * NightActivity constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this
            ->addOption('footer-spacing', 5)
            ->addOption('footer-center', ' _________________________________________________________________________________________________________________________________________
A = AWAKE      B = TO BATHROOM      DC = DIAPER CHANGE      S = SLEEPING      M = MEDICINE
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
        $residentsByTypeAndGroup = [];

        foreach ($residents as $resident) {
            if (!isset($residentsByTypeAndGroup[$resident['type']][$resident['typeId']][$resident['careGroup']]['name'])) {
                $residentsByTypeAndGroup[$resident['type']][$resident['typeId']][$resident['careGroup']]['name']      = $resident['name'];
                $residentsByTypeAndGroup[$resident['type']][$resident['typeId']][$resident['careGroup']]['groupName'] = $resident['careGroup'];
            }

            $residentsByTypeAndGroup[$resident['type']][$resident['typeId']][$resident['careGroup']]['data'][] = $resident;
        }

        $this->residents = $residentsByTypeAndGroup;
    }
}

