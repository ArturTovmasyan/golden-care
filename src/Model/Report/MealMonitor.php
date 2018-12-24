<?php

namespace App\Model\Report;

class MealMonitor extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * MealMonitor constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this
            ->addOption('orientation', self::ORIENTATION_LANDSCAPE)
            ->addOption('footer-left', 'Ciminocare Reports                    100%          75%          50%')
            ->addOption('footer-right', '<=Less than 50%          *="Other"                    Report page [page] of [topage] - ' . date('\ m/d/Y '));
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

