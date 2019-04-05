<?php

namespace App\Model\Report;

use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePersonRole;

class FaceSheet extends Base
{
    /**
     * @var array
     */
    private $residents = [];
    private $physicianPhones = [];
    private $responsiblePersonPhones = [];

    /**
     * FaceSheet constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addOption('footer-spacing', 4);
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
     * @param $medications
     */
    public function setMedications($medications): void
    {
        foreach ($medications as $medication) {
            $this->residents[$medication['residentId']]['medications'][] = $medication;
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
                'state' => $responsiblePerson->getResponsiblePerson() && $responsiblePerson->getResponsiblePerson()->getCsz() ? $responsiblePerson->getResponsiblePerson()->getCsz()->getStateFull() : '',
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
}

