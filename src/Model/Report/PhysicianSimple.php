<?php

namespace App\Model\Report;


class PhysicianSimple1 extends Base
{
    /**
     * @var array
     */
    private $physicianData;

    /**
     * @var string
     */
    private $type;

    /**
     * @return array
     */
    public function getPhysicianData()
    {
        return $this->physicianData;
    }

    /**
     * @param $physicianData
     */
    public function setPhysicianData($physicianData)
    {
        $this->physicianData = $physicianData;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }
}

