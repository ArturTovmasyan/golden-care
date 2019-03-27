<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_space")
 * @ORM\Entity(repositoryClass="App\Repository\SpaceRepository")
 * @UniqueEntity(fields="name", message="Sorry, this name is already in use.", groups={
 *     "api_admin_space_add", "api_admin_space_edit"
 * })
 *     api_admin_space_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "s.id"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          },
 *          {
 *              "id"         = "created_at",
 *              "type"       = "date",
 *              "field"      = "s.createdAt"
 *          }
 *     }
 * )
 */
class Space
{
    use TimeAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     *     "api_admin_space_grid",
     *     "api_admin_space_list",
     *     "api_admin_space_get",
     *     "api_profile_me",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_facility_list",
     *     "api_admin_facility_get",
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get",
     *     "api_admin_region_list",
     *     "api_admin_region_get",
     *     "api_admin_resident_grid",
     *     "api_admin_resident_list",
     *     "api_admin_resident_get",
     *     "api_admin_physician_speciality_list",
     *     "api_admin_physician_speciality_get",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get",
     *     "api_admin_assessment_category_list",
     *     "api_admin_assessment_category_get",
     *     "api_admin_assessment_care_level_list",
     *     "api_admin_assessment_care_level_get",
     *     "api_admin_assessment_care_level_group_list",
     *     "api_admin_assessment_care_level_group_get",
     *     "api_admin_assessment_form_list",
     *     "api_admin_assessment_form_get",
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get",
     *     "api_admin_salutation_list",
     *     "api_admin_salutation_get",
     *     "api_admin_city_state_zip_list",
     *     "api_admin_city_state_zip_get",
     *     "api_admin_care_level_list",
     *     "api_admin_care_level_get",
     *     "api_admin_relationship_list",
     *     "api_admin_relationship_get",
     *     "api_admin_medication_form_factor_list",
     *     "api_admin_medication_form_factor_get",
     *     "api_admin_medication_list",
     *     "api_admin_medication_get",
     *     "api_admin_medical_history_condition_list",
     *     "api_admin_medical_history_condition_get",
     *     "api_admin_diet_list",
     *     "api_admin_diet_get",
     *     "api_admin_diagnosis_list",
     *     "api_admin_diagnosis_get",
     *     "api_admin_allergen_grid",
     *     "api_admin_allergen_get",
     *     "api_admin_allergen_list",
     *     "api_admin_speciality_list",
     *     "api_admin_speciality_get",
     *     "api_admin_responsible_person_role_list",
     *     "api_admin_responsible_person_role_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", unique=true, length=255)
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     *     "api_admin_space_grid",
     *     "api_admin_space_list",
     *     "api_admin_space_get",
     *     "api_profile_me",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_facility_list",
     *     "api_admin_facility_get",
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get",
     *     "api_admin_region_list",
     *     "api_admin_region_get",
     *     "api_admin_physician_speciality_list",
     *     "api_admin_physician_speciality_get",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get",
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get",
     *     "api_admin_salutation_list",
     *     "api_admin_salutation_get",
     *     "api_admin_city_state_zip_list",
     *     "api_admin_city_state_zip_get",
     *     "api_admin_care_level_list",
     *     "api_admin_care_level_get",
     *     "api_admin_relationship_list",
     *     "api_admin_relationship_get",
     *     "api_admin_medication_form_factor_list",
     *     "api_admin_medication_form_factor_get",
     *     "api_admin_medication_list",
     *     "api_admin_medication_get",
     *     "api_admin_medical_history_condition_list",
     *     "api_admin_medical_history_condition_get",
     *     "api_admin_diet_list",
     *     "api_admin_diet_get",
     *     "api_admin_diagnosis_list",
     *     "api_admin_diagnosis_get",
     *     "api_admin_allergen_grid",
     *     "api_admin_allergen_get",
     *     "api_admin_allergen_list"
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_space_add",
     *     "api_admin_space_edit",
     *     "api_account_signup"
     * })
     */
    private $name;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="User", mappedBy="space", cascade={"persist", "remove"})
     */
    protected $users;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Physician", mappedBy="space", cascade={"persist", "remove"})
     */
    protected $physicians;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Allergen", mappedBy="space", cascade={"remove", "persist"})
     */
    private $allergens;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Apartment", mappedBy="space", cascade={"remove", "persist"})
     */
    private $apartments;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\CareLevel", mappedBy="space", cascade={"remove", "persist"})
     */
    private $careLevels;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\CityStateZip", mappedBy="space", cascade={"remove", "persist"})
     */
    private $cszs;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Diagnosis", mappedBy="space", cascade={"remove", "persist"})
     */
    private $diagnoses;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Diet", mappedBy="space", cascade={"remove", "persist"})
     */
    private $diets;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\EventDefinition", mappedBy="space", cascade={"remove", "persist"})
     */
    private $eventDefinitions;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Facility", mappedBy="space", cascade={"remove", "persist"})
     */
    private $facilities;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\MedicalHistoryCondition", mappedBy="space", cascade={"remove", "persist"})
     */
    private $historyConditions;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Medication", mappedBy="space", cascade={"remove", "persist"})
     */
    private $medications;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\MedicationFormFactor", mappedBy="space", cascade={"remove", "persist"})
     */
    private $formFactors;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\PaymentSource", mappedBy="space", cascade={"remove", "persist"})
     */
    private $paymentSources;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Region", mappedBy="space", cascade={"remove", "persist"})
     */
    private $regions;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Relationship", mappedBy="space", cascade={"remove", "persist"})
     */
    private $relationships;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Resident", mappedBy="space", cascade={"remove", "persist"})
     */
    private $residents;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResponsiblePerson", mappedBy="space", cascade={"remove", "persist"})
     */
    private $responsiblePersons;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResponsiblePersonRole", mappedBy="space", cascade={"remove", "persist"})
     */
    private $responsiblePersonRoles;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Salutation", mappedBy="space", cascade={"remove", "persist"})
     */
    private $salutations;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Speciality", mappedBy="space", cascade={"remove", "persist"})
     */
    private $specialities;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Assessment\CareLevelGroup", mappedBy="space", cascade={"remove", "persist"})
     */
    private $assessmentCareLevelGroups;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Assessment\Category", mappedBy="space", cascade={"remove", "persist"})
     */
    private $assessmentCategories;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Assessment\Form", mappedBy="space", cascade={"remove", "persist"})
     */
    private $assessmentForms;

    /**
     * Space constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers(): ?ArrayCollection
    {
        return $this->users;
    }

    /**
     * @param ArrayCollection $users
     */
    public function setUsers(?ArrayCollection $users): void
    {
        $this->users = $users;
    }

    /**
     * @return ArrayCollection
     */
    public function getPhysicians(): ?ArrayCollection
    {
        return $this->physicians;
    }

    /**
     * @param ArrayCollection $physicians
     */
    public function setPhysicians(?ArrayCollection $physicians): void
    {
        $this->physicians = $physicians;
    }

    /**
     * @return ArrayCollection
     */
    public function getAllergens(): ArrayCollection
    {
        return $this->allergens;
    }

    /**
     * @param ArrayCollection $allergens
     */
    public function setAllergens(ArrayCollection $allergens): void
    {
        $this->allergens = $allergens;
    }

    /**
     * @return ArrayCollection
     */
    public function getApartments(): ArrayCollection
    {
        return $this->apartments;
    }

    /**
     * @param ArrayCollection $apartments
     */
    public function setApartments(ArrayCollection $apartments): void
    {
        $this->apartments = $apartments;
    }

    /**
     * @return ArrayCollection
     */
    public function getCareLevels(): ArrayCollection
    {
        return $this->careLevels;
    }

    /**
     * @param ArrayCollection $careLevels
     */
    public function setCareLevels(ArrayCollection $careLevels): void
    {
        $this->careLevels = $careLevels;
    }

    /**
     * @return ArrayCollection
     */
    public function getCszs(): ArrayCollection
    {
        return $this->cszs;
    }

    /**
     * @param ArrayCollection $cszs
     */
    public function setCszs(ArrayCollection $cszs): void
    {
        $this->cszs = $cszs;
    }

    /**
     * @return ArrayCollection
     */
    public function getDiagnoses(): ArrayCollection
    {
        return $this->diagnoses;
    }

    /**
     * @param ArrayCollection $diagnoses
     */
    public function setDiagnoses(ArrayCollection $diagnoses): void
    {
        $this->diagnoses = $diagnoses;
    }

    /**
     * @return ArrayCollection
     */
    public function getDiets(): ArrayCollection
    {
        return $this->diets;
    }

    /**
     * @param ArrayCollection $diets
     */
    public function setDiets(ArrayCollection $diets): void
    {
        $this->diets = $diets;
    }

    /**
     * @return ArrayCollection
     */
    public function getEventDefinitions(): ArrayCollection
    {
        return $this->eventDefinitions;
    }

    /**
     * @param ArrayCollection $eventDefinitions
     */
    public function setEventDefinitions(ArrayCollection $eventDefinitions): void
    {
        $this->eventDefinitions = $eventDefinitions;
    }

    /**
     * @return ArrayCollection
     */
    public function getFacilities(): ArrayCollection
    {
        return $this->facilities;
    }

    /**
     * @param ArrayCollection $facilities
     */
    public function setFacilities(ArrayCollection $facilities): void
    {
        $this->facilities = $facilities;
    }

    /**
     * @return ArrayCollection
     */
    public function getHistoryConditions(): ArrayCollection
    {
        return $this->historyConditions;
    }

    /**
     * @param ArrayCollection $historyConditions
     */
    public function setHistoryConditions(ArrayCollection $historyConditions): void
    {
        $this->historyConditions = $historyConditions;
    }

    /**
     * @return ArrayCollection
     */
    public function getMedications(): ArrayCollection
    {
        return $this->medications;
    }

    /**
     * @param ArrayCollection $medications
     */
    public function setMedications(ArrayCollection $medications): void
    {
        $this->medications = $medications;
    }

    /**
     * @return ArrayCollection
     */
    public function getFormFactors(): ArrayCollection
    {
        return $this->formFactors;
    }

    /**
     * @param ArrayCollection $formFactors
     */
    public function setFormFactors(ArrayCollection $formFactors): void
    {
        $this->formFactors = $formFactors;
    }

    /**
     * @return ArrayCollection
     */
    public function getPaymentSources(): ArrayCollection
    {
        return $this->paymentSources;
    }

    /**
     * @param ArrayCollection $paymentSources
     */
    public function setPaymentSources(ArrayCollection $paymentSources): void
    {
        $this->paymentSources = $paymentSources;
    }

    /**
     * @return ArrayCollection
     */
    public function getRegions(): ArrayCollection
    {
        return $this->regions;
    }

    /**
     * @param ArrayCollection $regions
     */
    public function setRegions(ArrayCollection $regions): void
    {
        $this->regions = $regions;
    }

    /**
     * @return ArrayCollection
     */
    public function getRelationships(): ArrayCollection
    {
        return $this->relationships;
    }

    /**
     * @param ArrayCollection $relationships
     */
    public function setRelationships(ArrayCollection $relationships): void
    {
        $this->relationships = $relationships;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidents(): ArrayCollection
    {
        return $this->residents;
    }

    /**
     * @param ArrayCollection $residents
     */
    public function setResidents(ArrayCollection $residents): void
    {
        $this->residents = $residents;
    }

    /**
     * @return ArrayCollection
     */
    public function getResponsiblePersons(): ArrayCollection
    {
        return $this->responsiblePersons;
    }

    /**
     * @param ArrayCollection $responsiblePersons
     */
    public function setResponsiblePersons(ArrayCollection $responsiblePersons): void
    {
        $this->responsiblePersons = $responsiblePersons;
    }

    /**
     * @return ArrayCollection
     */
    public function getResponsiblePersonRoles(): ArrayCollection
    {
        return $this->responsiblePersonRoles;
    }

    /**
     * @param ArrayCollection $responsiblePersonRoles
     */
    public function setResponsiblePersonRoles(ArrayCollection $responsiblePersonRoles): void
    {
        $this->responsiblePersonRoles = $responsiblePersonRoles;
    }

    /**
     * @return ArrayCollection
     */
    public function getSalutations(): ArrayCollection
    {
        return $this->salutations;
    }

    /**
     * @param ArrayCollection $salutations
     */
    public function setSalutations(ArrayCollection $salutations): void
    {
        $this->salutations = $salutations;
    }

    /**
     * @return ArrayCollection
     */
    public function getSpecialities(): ArrayCollection
    {
        return $this->specialities;
    }

    /**
     * @param ArrayCollection $specialities
     */
    public function setSpecialities(ArrayCollection $specialities): void
    {
        $this->specialities = $specialities;
    }

    /**
     * @return ArrayCollection
     */
    public function getAssessmentCareLevelGroups(): ArrayCollection
    {
        return $this->assessmentCareLevelGroups;
    }

    /**
     * @param ArrayCollection $assessmentCareLevelGroups
     */
    public function setAssessmentCareLevelGroups(ArrayCollection $assessmentCareLevelGroups): void
    {
        $this->assessmentCareLevelGroups = $assessmentCareLevelGroups;
    }

    /**
     * @return ArrayCollection
     */
    public function getAssessmentCategories(): ArrayCollection
    {
        return $this->assessmentCategories;
    }

    /**
     * @param ArrayCollection $assessmentCategories
     */
    public function setAssessmentCategories(ArrayCollection $assessmentCategories): void
    {
        $this->assessmentCategories = $assessmentCategories;
    }

    /**
     * @return ArrayCollection
     */
    public function getAssessmentForms(): ArrayCollection
    {
        return $this->assessmentForms;
    }

    /**
     * @param ArrayCollection $assessmentForms
     */
    public function setAssessmentForms(ArrayCollection $assessmentForms): void
    {
        $this->assessmentForms = $assessmentForms;
    }
}
