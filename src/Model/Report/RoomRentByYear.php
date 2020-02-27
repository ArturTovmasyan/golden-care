<?php

namespace App\Model\Report;

class RoomRentByYear extends Base
{
    /**
     * @var array
     */
    private $data = [];
    private $csvData = [];
    private $calcAmount = [];
    private $place = [];
    private $total = [];
    private $residentCount = [];

    /**
     * @var string
     */
    private $strategy;

    /**
     * @var int
     */
    private $strategyId;

    /**
     * RoomRent constructor.
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
     * @param $residentCount
     */
    public function setResidentCount($residentCount): void
    {
        $this->residentCount = $residentCount;
    }

    /**
     * @return array
     */
    public function getResidentCount(): ?array
    {
        return $this->residentCount;
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

