<?php

namespace App\Model\Report;

class BowelMovement extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * BowelMovement constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this
            ->addOption('footer-spacing', 6)
            ->addOption('footer-left', '_________________________________________________________________
Indicate size of stool
S=Small  M=Medium  L=Large  N=None
Ciminocare Reports')
            ->addOption('footer-right', '______________________________________________________________________
Indicate consistency of stool
D=Diarrhea or Loose   B=Bloody or Dark
Report page [page] of [topage] - ' . date('\ m/d/Y '));
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

