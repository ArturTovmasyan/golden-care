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
}

