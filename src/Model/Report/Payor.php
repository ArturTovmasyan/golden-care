<?php

namespace App\Model\Report;

class Payor extends Base
{
    /**
     * @var array
     */
    private $data = [];
    private $calcAmount = [];
    private $place = [];
    private $total = [];
    private $sources = [];

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
    private $date;

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
     * @param $sources
     */
    public function setSources($sources): void
    {
        $this->sources = $sources;
    }

    /**
     * @return array
     */
    public function getSources(): ?array
    {
        return $this->sources;
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
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate(string $date): void
    {
        $this->date = $date;
    }
}

