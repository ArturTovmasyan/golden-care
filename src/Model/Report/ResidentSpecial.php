<?php

namespace App\Model\Report;

use App\Entity\ResidentResponsiblePerson;
use App\Entity\ResponsiblePerson;
use App\Entity\ResponsiblePersonRole;
use App\Model\AdmissionType;
use App\Model\DiagnosisType;
use App\Model\GroupType;
use App\Model\Phone;

class ResidentSpecial extends Base
{
    /**
     * @var array
     */
    private $residents = [];
    private $physicianPhones = [];
    private $responsiblePersonPhones = [];

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
     * @param $admissions
     */
    public function setAdmissions($admissions): void
    {
        foreach ($admissions as $admission) {
            $admission['type'] = GroupType::getTypes()[$admission['type']];
            $admission['admissionType'] = AdmissionType::getTypes()[$admission['admissionType']];

            $this->residents[$admission['residentId']]['admissions'][] = $admission;
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
     * @param $conditions
     */
    public function setConditions($conditions): void
    {
        foreach ($conditions as $condition) {
            $this->residents[$condition['residentId']]['conditions'][] = $condition;
        }
    }

    /**
     * @param $diagnoses
     */
    public function setDiagnosis($diagnoses): void
    {
        foreach ($diagnoses as $diagnosis) {
            $diagnosis['diagnosisType'] = DiagnosisType::getTypeNames()[$diagnosis['diagnosisType']];

            $this->residents[$diagnosis['residentId']]['diagnosis'][] = $diagnosis;
        }
    }

    /**
     * @param $diets
     */
    public function setDiets($diets): void
    {
        foreach ($diets as $diet) {
            $this->residents[$diet['residentId']]['diets'][] = $diet;
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
     * @param $insurances
     */
    public function setInsurances($insurances): void
    {
        if (!empty($insurances)) {
            foreach ($insurances as $insurance) {
                $this->residents[$insurance['residentId']]['insurances'][] = $insurance;
            }
        }
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
     * @param $medicationAllergens
     */
    public function setMedicationAllergens($medicationAllergens): void
    {
        foreach ($medicationAllergens as $medicationAllergen) {
            $this->residents[$medicationAllergen['residentId']]['medicationAllergens'][] = $medicationAllergen;
        }
    }

    /**
     * @param $physicians
     */
    public function setPhysicians($physicians): void
    {
        foreach ($physicians as $physician) {
            if (!empty($this->getPhysicianPhones())) {
                foreach ($this->getPhysicianPhones() as $physicianPhone) {
                    if ($physicianPhone['pId'] ===  $physician['pId']) {
                        $physicianPhone['type'] = Phone::getTypeNames()[$physicianPhone['type']];
                        $physicianPhone['extension'] = !empty($physicianPhone['extension']) ? $physicianPhone['extension'] : '';

                        $physician['phones'][] = $physicianPhone;
                    }
                }
            }

            $this->residents[$physician['residentId']]['physicians'][] = $physician;
        }
    }

    /**
     * @param $rents
     */
    public function setRents($rents): void
    {
        foreach ($rents as $rent) {
            $rent['start'] = $rent['start']->format('Y-m-d H:i:s');

            $this->residents[$rent['residentId']]['rents'][] = $rent;
        }
    }

    /**
     * @param $responsiblePersons
     */
    public function setResponsiblePersons($responsiblePersons): void
    {
        /** @var ResidentResponsiblePerson $responsiblePerson */
        foreach ($responsiblePersons as $responsiblePerson) {
            $phones = [];
            if (!empty($this->getResponsiblePersonPhones())) {
                foreach ($this->getResponsiblePersonPhones() as $responsiblePersonPhone) {
                    if ($responsiblePersonPhone['rpId'] ===  $responsiblePerson->getResponsiblePerson()->getId()) {
                        $responsiblePersonPhone['type'] = Phone::getTypeNames()[$responsiblePersonPhone['type']];
                        $responsiblePersonPhone['extension'] = !empty($responsiblePersonPhone['extension']) ? $responsiblePersonPhone['extension'] : '';

                        $phones[] = $responsiblePersonPhone;
                    }
                }
            }

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
                'financially' => $isFinancially,
                'phones' => $phones
            ];
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

