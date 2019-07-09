<?php

namespace App\Model\Report;

class MedicationChartBlank extends Base
{
    /**
     * @var
     */
    private $object;

    /**
     * MedicationChartBlank constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addOption('orientation', self::ORIENTATION_LANDSCAPE);
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param mixed $object
     */
    public function setObject($object): void
    {
        $this->object = $object;
    }
}

