<?php

namespace App\Model\Report;

class PhysicianFull extends Base
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $count = [];

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
     * @param $count
     */
    public function setCount($count): void
    {
        $this->count = $count;
    }

    /**
     * @return array
     */
    public function getCount(): ?array
    {
        return $this->count;
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

