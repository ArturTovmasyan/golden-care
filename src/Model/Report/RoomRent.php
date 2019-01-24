<?php

namespace App\Model\Report;

class RoomRent extends Base
{
    /**
     * @var array
     */
    private $data = [];
    private $csvData = [];
    private $calcAmount = [];
    private $place = [];
    private $total = [];
    private $responsiblePersons = [];

    /**
     * @var string
     */
    private $strategy;

    /**
     * @var int
     */
    private $strategyId;

    /**
     * @var string
     */
    private $dateStart;

    /**
     * @var string
     */
    private $dateEnd;

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

    /**
     * @param $csvData
     */
    public function setCsvData($csvData): void
    {
        $this->csvData = $csvData;
    }

    /**
     * @return array
     */
    public function getCsvData(): ?array
    {
        return $this->csvData;
    }

    /**
     * @param $calcAmount
     */
    public function setCalcAmount($calcAmount): void
    {
        $this->calcAmount = $calcAmount;
    }

    /**
     * @return array
     */
    public function getCalcAmount(): ?array
    {
        return $this->calcAmount;
    }

    /**
     * @param $place
     */
    public function setPlace($place): void
    {
        $this->place = $place;
    }

    /**
     * @return array
     */
    public function getPlace(): ?array
    {
        return $this->place;
    }

    /**
     * @param $total
     */
    public function setTotal($total): void
    {
        $this->total = $total;
    }

    /**
     * @return array
     */
    public function getTotal(): ?array
    {
        return $this->total;
    }

    /**
     * @param $responsiblePersons
     */
    public function setResponsiblePersons($responsiblePersons): void
    {
        $this->responsiblePersons = $responsiblePersons;
    }

    /**
     * @return array
     */
    public function getResponsiblePersons(): ?array
    {
        return $this->responsiblePersons;
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

    /**
     * @return string
     */
    public function getDateStart(): string
    {
        return $this->dateStart;
    }

    /**
     * @param string $dateStart
     */
    public function setDateStart(string $dateStart): void
    {
        $this->dateStart = $dateStart;
    }

    /**
     * @return string
     */
    public function getDateEnd(): string
    {
        return $this->dateEnd;
    }

    /**
     * @param string $dateEnd
     */
    public function setDateEnd(string $dateEnd): void
    {
        $this->dateEnd = $dateEnd;
    }
}

