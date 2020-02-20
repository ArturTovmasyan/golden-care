<?php

namespace App\Model\Report;

class MissingRentRecords extends Base
{
    /**
     * @var array
     */
    private $residents = [];
    private $rentResidentIds = [];
    private $endDateInThePastIds = [];
    private $moreThanOneEndDateNullIds = [];
    private $overlapIds = [];

    /**
     * @var string
     */
    private $strategy;

    /**
     * @var int
     */
    private $strategyId;

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

    /**
     * @param $rentResidentIds
     */
    public function setRentResidentIds($rentResidentIds): void
    {
        $this->rentResidentIds = $rentResidentIds;
    }

    /**
     * @return array
     */
    public function getRentResidentIds(): ?array
    {
        return $this->rentResidentIds;
    }

    /**
     * @param $endDateInThePastIds
     */
    public function setEndDateInThePastIds($endDateInThePastIds): void
    {
        $this->endDateInThePastIds = $endDateInThePastIds;
    }

    /**
     * @return array
     */
    public function getEndDateInThePastIds(): ?array
    {
        return $this->endDateInThePastIds;
    }

    /**
     * @param $moreThanOneEndDateNullIds
     */
    public function setMoreThanOneEndDateNullIds($moreThanOneEndDateNullIds): void
    {
        $this->moreThanOneEndDateNullIds = $moreThanOneEndDateNullIds;
    }

    /**
     * @return array
     */
    public function getMoreThanOneEndDateNullIds(): ?array
    {
        return $this->moreThanOneEndDateNullIds;
    }

    /**
     * @param $overlapIds
     */
    public function setOverlapIds($overlapIds): void
    {
        $this->overlapIds = $overlapIds;
    }

    /**
     * @return array
     */
    public function getOverlapIds(): ?array
    {
        return $this->overlapIds;
    }

    /**
     * @param $strategy
     */
    public function setStrategy($strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @return mixed
     */
    public function getStrategy()
    {
        return $this->strategy;
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

