<?php

namespace App\Model\Report;

use App\Entity\ResponsiblePerson;

class ResidentEvent extends Base
{
    /**
     * @var array
     */
    private $residents = [];

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
     * @param $events
     */
    public function setEvents($events): void
    {
        /** @var \App\Entity\ResidentEvent $event */
        foreach ($events as $event) {
            $responsiblePersons = [];
            if (!empty($event->getResponsiblePersons())) {
                /** @var ResponsiblePerson $responsiblePerson */
                foreach ($event->getResponsiblePersons() as $responsiblePerson) {
                    $responsiblePersons[] = [
                        'fullName' => $responsiblePerson->getFirstName() . ' ' . $responsiblePerson->getLastName(),
                        'salutation' => $responsiblePerson->getSalutation() ? $responsiblePerson->getSalutation()->getTitle() : '',
                    ];
                }
            }

            $residentId = $event->getResident() ? $event->getResident()->getId() : 0;

            $this->residents[$residentId]['events'][] = [
                'id' => $residentId,
                'residentId' => $event->getId(),
                'title' => $event->getDefinition() ? $event->getDefinition()->getTitle() : 'N/A',
                'date' => $event->getDate(),
                'additionalDate' => $event->getAdditionalDate() ?? '',
                'notes' => $event->getNotes() ?? 'N/A',
                'physicianFullName' => $event->getPhysician() ? $event->getPhysician()->getFirstName() . ' ' . $event->getPhysician()->getLastName() : '',
                'physicianSalutation' => $event->getPhysician() && $event->getPhysician()->getSalutation() ? $event->getPhysician()->getSalutation()->getTitle() : '',
                'responsiblePersons' => $responsiblePersons
            ];
        }
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

