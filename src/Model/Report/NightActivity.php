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

