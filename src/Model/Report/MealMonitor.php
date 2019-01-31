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