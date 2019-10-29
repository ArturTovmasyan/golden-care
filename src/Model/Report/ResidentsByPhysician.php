<?php

namespace App\Model\Report;

class ResidentsByPhysician extends Base
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array
     */
    private $physicianPhones = [];

    /**
     * @var array
     */
    private $count = [];

    /**
     * @var array
     */
    private $residents = [];

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
     * @param $physicianPhones
     */
    public function setPhysicianPhones($physicianPhones): void
    {
        $this->physicianPhones = $physicianPhones;
    }

    /**
     * @return array
     */
    public function getPhysicianPhones(): ?array
    {
        return $this->physicianPhones;
    }
}

