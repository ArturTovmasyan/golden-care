<?php

namespace App\Model\Report;

use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePerson;
use App\Entity\ResponsiblePersonRole;

class Profile extends Base
{
    /**
     * @var array
     */
    private $residents = [];
    private $physicianPhones = [];
    private $responsiblePersonPhones = [];
    private $insuranceFiles = [];
    private $images = [];

    /**
     * @var string
     */
    private $strategy;

    /**
     * @var boolean
     */
    private $discontinued;

    /**
     * Profile constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addOption('footer-spacing', 4);
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
     * @param $insurances
     */
    public function setInsurances($insurances): void
    {
        foreach ($insurances as $insurance) {
            $this->residents[$insurance['residentId']]['insurances'][] = $insurance;
        }
    }

    /**
     * @return array
     */
    public function getInsuranceFiles(): ?array
    {
        return $this->insuranceFiles;
    }

    /**
     * @param $insuranceFiles
     */
    public function setInsuranceFiles($insuranceFiles): void
    {
        $this->insuranceFiles = $insuranceFiles;
    }

    /**
     * @return array
     */
    public function getImages(): ?array
    {
        return $this->images;
    }

    /**
     * @param $images
     */
    public function setImages($images): void
    {
        $this->images = $images;
    }

    /**
     * @param $medications
     */
    public function setMedications($medications): void
    {
        foreach ($medications as $medication) {
            if ($medication['medicationDiscont']) {
                $this->residents[$medication['residentId']]['medications']['discontinued'][] = $medication;
            } else {
                $this->residents[$medication['residentId']]['medications']['active'][] = $medication;
            }
        }
    }

    /**
     * @param $allergens
     */
    public function setAllergens($allergens): void
    {
        foreach ($allergens as $allergen) {
            $this->residents[$allergen['residentId']]['allergens'][] = $allergen;
        }
    }

    /**
     * @param $diagnoses
     */
    public function setDiagnosis($diagnoses): void
    {
        foreach ($diagnoses as $diagnosis) {
            $this->residents[$diagnosis['residentId']]['diagnosis'][] = $diagnosis;
        }
    }

    /**
     * @param $responsiblePersons
     */
    public function setResponsiblePersons($responsiblePersons): void
    {
        /** @var ResidentResponsiblePerson $responsiblePerson */
        foreach ($responsiblePersons as $responsiblePerson) {
            $isEmergency = false;
            $isFinancially = false;

            if (!empty($responsiblePerson->getRoles())) {
                /** @var ResponsiblePersonRole $role */
                foreach ($responsiblePerson->getRoles() as $role) {
                    if ($role->isEmergency() === true) {
                        $isEmergency = true;
                    }

                    if ($role->isFinancially() === true) {
                        $isFinancially = true;
                    }
                }
            }

            $residentId = $responsiblePerson->getResident() ? $responsiblePerson->getResident()->getId() : 0;

            $this->residents[$residentId]['responsiblePersons'][] = [
                'residentId' => $residentId,
                'id' => $responsiblePerson->getId(),
                'responsiblePersonFullName' => $responsiblePerson->getResponsiblePerson() ? $responsiblePerson->getResponsiblePerson()->getFirstName() . ' ' . $responsiblePerson->getResponsiblePerson()->getLastName() : '',
                'rpId' => $responsiblePerson->getResponsiblePerson() ? $responsiblePerson->getResponsiblePerson()->getId() : 0,
                'address' => $responsiblePerson->getResponsiblePerson() ? $responsiblePerson->getResponsiblePerson()->getAddress1() : '',
                'state' => $responsiblePerson->getResponsiblePerson() && $responsiblePerson->getResponsiblePerson()->getCsz() ? $responsiblePerson->getResponsiblePerson()->getCsz()->getStateAbbr() : '',
                'zip' => $responsiblePerson->getResponsiblePerson() && $responsiblePerson->getResponsiblePerson()->getCsz() ? $responsiblePerson->getResponsiblePerson()->getCsz()->getZipMain() : '',
                'city' => $responsiblePerson->getResponsiblePerson() && $responsiblePerson->getResponsiblePerson()->getCsz() ? $responsiblePerson->getResponsiblePerson()->getCsz()->getCity() : '',
                'relationshipTitle' => $responsiblePerson->getRelationship() ? $responsiblePerson->getRelationship()->getTitle() : '',
                'emergency' => $isEmergency,
                'financially' => $isFinancially
            ];
        }
    }

    /**
     * @param $physicians
     */
    public function setPhysicians($physicians): void
    {
        foreach ($physicians as $physician) {
            $this->residents[$physician['residentId']]['physicians'][] = $physician;
        }
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

    /**
     * @param $responsiblePersonPhones
     */
    public function setResponsiblePersonPhones($responsiblePersonPhones): void
    {
        $this->responsiblePersonPhones = $responsiblePersonPhones;
    }

    /**
     * @return array
     */
    public function getResponsiblePersonPhones(): ?array
    {
        return $this->responsiblePersonPhones;
    }

    /**
     * @param $admissions
     */
    public function setAdmissions($admissions): void
    {
        foreach ($admissions as $admission) {
            $this->residents[$admission['residentId']]['admissions'][] = $admission;
        }
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
                'residentId' => $residentId,
                'id' => $event->getId(),
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
     * @param $rents
     */
    public function setRents($rents): void
    {
        foreach ($rents as $rent) {
            $this->residents[$rent['residentId']]['rents'][] = $rent;
        }
    }

    /**
     * @return bool
     */
    public function isDiscontinued(): bool
    {
        return $this->discontinued;
    }

    /**
     * @param bool $discontinued
     */
    public function setDiscontinued(bool $discontinued): void
    {
        $this->discontinued = $discontinued;
    }
}

