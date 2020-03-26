<?php

namespace App\Model\Report;

class ResponsiblePersonEmails extends Base
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $strategy;

    /**
     * @var int
     */
    private $strategyId;

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

