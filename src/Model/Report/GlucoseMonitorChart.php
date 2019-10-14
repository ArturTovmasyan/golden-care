<?php

namespace App\Model\Report;

class GlucoseMonitorChart extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * GlucoseMonitorChart constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this
            ->addOption('orientation', self::ORIENTATION_LANDSCAPE);
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

