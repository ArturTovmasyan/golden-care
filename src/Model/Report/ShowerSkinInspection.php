<?php

namespace App\Model\Report;

class ShowerSkinInspection extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * ShowerSkinInspection constructor.
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

