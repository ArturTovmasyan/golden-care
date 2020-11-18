<?php

namespace App\Model\Report;

class ResidentCovidEvent extends Base
{
    /**
     * @var array
     */
    private $events = [];

    /**
     * @var string
     */
    private $strategy;

    /**
     * @var string
     */
    private $startDate;

    /**
     * @var string
     */
    private $endDate;

    /**
     * @param $events
     */
    public function setEvents($events): void
    {
        $this->events = $events;
    }

    /**
     * @return array
     */
    public function getEvents(): ?array
    {
        return $this->events;
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
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     */
    public function setStartDate(string $startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->endDate;
    }

    /**
     * @param string $endDate
     */
    public function setEndDate(string $endDate): void
    {
        $this->endDate = $endDate;
    }
}

