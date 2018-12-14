<?php

namespace App\Model\Report;

class BowelMovement extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    private $options = [];

    /**
     * BowelMovement constructor.
     */
    public function __construct()
    {
        $this->options = [
            'footer-spacing' => 6,
            'footer-left'    => '_________________________________________________________________
Indicate size of stool
S=Small  M=Medium  L=Large  N=None
Ciminocare Reports',
            'footer-right' => '______________________________________________________________________
Indicate consistency of stool
D=Diarrhea or Loose   B=Bloody or Dark
Report page [page] of [topage] - ' . date('\ m/d/Y ')
        ];
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
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

