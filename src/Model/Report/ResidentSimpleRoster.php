<?php

namespace App\Model\Report;

class ResidentSimpleRoster extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * @var array
     */
    private $typeIds = [];

    /**
     * @var array
     */
    private $numberOfFloors = [];

    /**
     * @var int
     */
    private $strategyId;

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
    public function setResidents($residents): void
    {
        $this->residents = $residents;
    }

    /**
     * @return mixed
     */
    public function getTypeIds()
    {
        return $this->typeIds;
    }

    /**
     * @param $typeIds
     */
    public function setTypeIds($typeIds): void
    {
        $this->typeIds = $typeIds;
    }

    /**
     * @return mixed
     */
    public function getNumberOfFloors()
    {
        return $this->numberOfFloors;
    }

    /**
     * @param $numberOfFloors
     */
    public function setNumberOfFloors($numberOfFloors): void
    {
        $this->numberOfFloors = $numberOfFloors;
    }

    /**
     * @param $strategyId
     */
    public function setStrategyId($strategyId): void
    {
        $this->strategyId = $strategyId;
    }

    /**
     * @return mixed
     */
    public function getStrategyId()
    {
        return $this->strategyId;
    }
}

