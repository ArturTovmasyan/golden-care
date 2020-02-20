<?php

namespace App\Model\Report;

class ResidentMoveByMonth extends Base
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $days = [];

    /**
     * @var array
     */
    private $grandTotal = [];

    /**
     * @var string
     */
    private $strategy;

    /**
     * @var int
     */
    private $strategyId;

    /**
     * ResidentMoveByMonth constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addOption('orientation', self::ORIENTATION_LANDSCAPE);
        $this->addOption('footer-spacing', 5);
        $this->addOption('footer-center', ' _________________________________________________________________________________________________________________________________________
(*) Calculated based on 1 year = 365 days, 1 month = 30 days
                                                    ');
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
     * @param $days
     */
    public function setDays($days): void
    {
        $this->days = $days;
    }

    /**
     * @return array
     */
    public function getDays(): ?array
    {
        return $this->days;
    }

    /**
     * @param $grandTotal
     */
    public function setGrandTotal($grandTotal): void
    {
        $this->grandTotal = $grandTotal;
    }

    /**
     * @return array
     */
    public function getGrandTotal(): ?array
    {
        return $this->grandTotal;
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

