<?php

namespace App\Model\Report;

class FacilityCareLevels extends Base
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * FacilityCareLevels constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addOption('orientation', self::ORIENTATION_LANDSCAPE);
    }

    /**
     * @param $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): ?array
    {
        return $this->data;
    }
}

