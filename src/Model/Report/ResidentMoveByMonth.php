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

