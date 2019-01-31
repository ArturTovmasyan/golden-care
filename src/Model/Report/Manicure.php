<?php

namespace App\Model\Report;

class Manicure extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * Manicure constructor.
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

