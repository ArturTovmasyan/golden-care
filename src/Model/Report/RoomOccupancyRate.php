<?php

namespace App\Model\Report;

class RoomOccupancyRate extends Base
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
}

