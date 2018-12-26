<?php

namespace App\Model\Report;

use App\Model\ContractType;

class ResidentEvent extends Base
{
    /**
     * @var array
     */
    private $events = [];

    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param $events
     */
    public function setEvents($events)
    {
        $eventsByType = [];

        foreach ($events as $event) {
            $start = $eventsByType[$event['type']][$event['typeId']]['start'] ?? null;
            $end   = $eventsByType[$event['type']][$event['typeId']]['end'] ?? null;

            if (is_null($start) || $start->diff($event['startDate']) > 0) {
                $eventsByType[$event['type']][$event['typeId']]['start'] = $event['startDate'];
            }

            if (is_null($end) || ($event['endDate'] instanceof \DateTime && $event['endDate']->diff($end) > 0)) {
                $eventsByType[$event['type']][$event['typeId']]['end'] = $event['endDate'];
            }

            if (!isset($eventsByType[$event['type']][$event['typeId']]['name'])) {
                $eventsByType[$event['type']][$event['typeId']]['name']      = ContractType::getTypes()[$event['type']] ;
                $eventsByType[$event['type']][$event['typeId']]['shorthand'] = $event['shorthand'];
            }

            $eventsByType[$event['type']][$event['typeId']]['data'][$event['residentId']][] = $event;
        }

        $this->events = $eventsByType;
    }
}

