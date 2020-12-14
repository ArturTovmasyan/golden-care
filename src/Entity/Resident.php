<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use App\Annotation\Grid;

/**
 * @ORM\Table(name="tbl_resident")
 * @ORM\Entity(repositoryClass="App\Repository\ResidentRepository")
 * @Grid(
 *     api_admin_resident_compact_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "r.id"
 *          },
 *          {
 *              "id"         = "room",
 *              "type"       = "string",
 *              "field"      = "room"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(sal.title,''), ' ', COALESCE(r.firstName, ''), ' ', COALESCE(r.middleName, ''), ' ', COALESCE(r.lastName, ''))",
 *              "link"       = "/resident/:id"
 *          },
 *          {
 *              "id"         = "start",
 *              "type"       = "date",
 *              "field"      = "start"
 *          },
 *          {
 *              "id"         = "address",
 *              "type"       = "string",
 *              "field"      = "address"
 *          },
 *          {
 *              "id"         = "csz_str",
 *              "type"       = "string",
 *              "field"      = "csz_str"
 *          },
 *          {
 *              "id"         = "group_name",
 *              "type"       = "string",
 *              "field"      = "group_name"
 *          },
 *     },
 *     api_admin_resident_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "r.id"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(sal.title,''), ' ', COALESCE(r.firstName, ''), ' ', COALESCE(r.middleName, ''), ' ', COALESCE(r.lastName, ''))",
 *              "link"       = "/resident/:id"
 *          },
 *          {
 *              "id"         = "gender",
 *              "type"       = "enum",
 *              "field"      = "r.gender",
 *              "values"     = "\App\Model\User::genderValues"
 *          },
 *          {
 *              "id"         = "birthday",
 *              "type"       = "date",
 *              "field"      = "r.birthday"
 *          },
 *          {
 *              "id"         = "room",
 *              "type"       = "string",
 *              "field"      = "room"
 *          },
 *          {
 *              "id"         = "start",
 *              "type"       = "date",
 *              "field"      = "start"
 *          },
 *          {
 *              "id"         = "address",
 *              "type"       = "string",
 *              "field"      = "address"
 *          },
 *          {
 *              "id"         = "csz_str",
 *              "type"       = "string",
 *              "field"      = "csz_str"
 *          },
 *          {
 *              "id"         = "group_name",
 *              "type"       = "string",
 *              "field"      = "group_name"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class Resident
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_resident_diet_list",
     *      "api_admin_resident_diet_get",
     *      "api_admin_resident_medication_list",
     *      "api_admin_resident_medication_get",
     *      "api_admin_resident_medication_allergy_list",
     *      "api_admin_resident_medication_allergy_get",
     *      "api_admin_resident_allergen_list",
     *      "api_admin_resident_allergen_get",
     *      "api_admin_resident_medical_history_condition_list",
     *      "api_admin_resident_medical_history_condition_get",
     *      "api_admin_resident_diagnosis_list",
     *      "api_admin_resident_diagnosis_get",
     *      "api_admin_resident_list_by_params",
     *      "api_admin_resident_responsible_person_list",
     *      "api_admin_resident_responsible_person_get",
     *      "api_admin_resident_physician_list",
     *      "api_admin_resident_physician_get",
     *      "api_admin_resident_rent_list",
     *      "api_admin_resident_rent_get",
     *      "api_admin_resident_event_list",
     *      "api_admin_resident_event_get",
     *      "api_admin_resident_admission_list",
     *      "api_admin_resident_admission_get",
     *      "api_admin_contract_list",
     *      "api_admin_contract_get",
     *      "api_admin_facility_room_list",
     *      "api_admin_facility_room_get",
     *      "api_admin_facility_bed_list",
     *      "api_admin_apartment_room_list",
     *      "api_admin_apartment_room_get",
     *      "api_admin_apartment_bed_list",
     *      "api_admin_resident_health_insurance_list",
     *      "api_admin_resident_health_insurance_get",
     *      "api_admin_resident_document_list",
     *      "api_admin_resident_document_get",
     *      "api_admin_resident_rent_increase_list",
     *      "api_admin_resident_rent_increase_get",
     *      "api_admin_facility_event_list",
     *      "api_admin_facility_event_get",
     *      "api_admin_resident_ledger_list",
     *      "api_admin_resident_ledger_get",
     *      "api_admin_resident_expense_item_list",
     *      "api_admin_resident_expense_item_get",
     *      "api_admin_resident_away_days_list",
     *      "api_admin_resident_away_days_get"
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Space", inversedBy="residents", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_get"
     * })
     */
    private $space;

    /**
     * @ORM\ManyToOne(targetEntity="Salutation", inversedBy="residents", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_salutation", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_resident_event_get",
     *      "api_admin_resident_rent_get",
     *      "api_admin_resident_rent_increase_get"
     * })
     */
    private $salutation;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "First Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_add",
     *          "api_admin_resident_edit"
     *      }
     * )
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_resident_list_by_params",
     *      "api_admin_resident_responsible_person_list",
     *      "api_admin_resident_responsible_person_get",
     *      "api_admin_resident_physician_list",
     *      "api_admin_resident_physician_get",
     *      "api_admin_resident_health_insurance_list",
     *      "api_admin_resident_health_insurance_get",
     *      "api_admin_resident_document_list",
     *      "api_admin_resident_document_get",
     *      "api_admin_facility_event_list",
     *      "api_admin_facility_event_get",
     *      "api_admin_resident_event_get",
     *      "api_admin_resident_rent_get",
     *      "api_admin_resident_rent_increase_get",
     *      "api_admin_facility_bed_list",
     *      "api_admin_apartment_bed_list",
     *      "api_admin_resident_ledger_get"
     * })
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "Last Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_add",
     *          "api_admin_resident_edit"
     *      }
     * )
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_resident_list_by_params",
     *      "api_admin_resident_responsible_person_list",
     *      "api_admin_resident_responsible_person_get",
     *      "api_admin_resident_physician_list",
     *      "api_admin_resident_physician_get",
     *      "api_admin_resident_health_insurance_list",
     *      "api_admin_resident_health_insurance_get",
     *      "api_admin_resident_document_list",
     *      "api_admin_resident_document_get",
     *      "api_admin_facility_event_list",
     *      "api_admin_facility_event_get",
     *      "api_admin_resident_event_get",
     *      "api_admin_resident_rent_get",
     *      "api_admin_resident_rent_increase_get",
     *      "api_admin_facility_bed_list",
     *      "api_admin_apartment_bed_list",
     *      "api_admin_resident_ledger_get"
     * })
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(name="middle_name", type="string", length=60, nullable=true)
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "Middle Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_add",
     *          "api_admin_resident_edit"
     *      }
     * )
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $middleName;

    /**
     * @var \DateTime
     * @ORM\Column(name="birthday", type="date")
     * @Assert\Date(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_get"
     * })
     */
    private $birthday;

    /**
     * @var int
     * @ORM\Column(name="gender", type="smallint", nullable=false)
     * @Assert\Choice(
     *      callback={"App\Model\User","getGenderValues"},
     *      groups={
     *          "api_admin_resident_add",
     *          "api_admin_resident_edit"
     *      }
     * )
     * @Groups({
     *      "api_admin_resident_get"
     * })
     */
    private $gender;

    /**
     * @var string $ssn
     * @ORM\Column(name="social_security_number", type="string", length=11, nullable=true)
     * @Assert\Regex(
     *      pattern="/^[\dX]{3}-?[\dX]{2}-?[\dX]{4}$/",
     *      message="Invalid SSN number",
     *      groups={
     *          "api_admin_resident_add",
     *          "api_admin_resident_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 11,
     *      maxMessage = "SSN number cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_add",
     *          "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_get"
     * })
     */
    private $ssn;

    /**
     * @ORM\OneToMany(targetEntity="ResidentPhone", mappedBy="resident")
     * @Assert\Valid(groups={
     *      "api_admin_resident_add",
     *      "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_get"
     * })
     */
    private $phones;

    /**
     * @var Image
     * @ORM\OneToOne(targetEntity="App\Entity\Image", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $image;

    /**
     * @var string $downloadUrl
     */
    private $downloadUrl;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("image")
     * @Serializer\Groups({
     *     "api_admin_resident_list",
     *     "api_admin_resident_get"
     * })
     * @return null|string
     */
    public function getResidentImage(): ?string
    {
        if ($this->getImage() !== null) {
            return $this->getDownloadUrl();
        }

        return null;
    }

    /**
     * @var string $downloadString
     */
    private $downloadString;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("photo")
     * @Serializer\Groups({
     *     "api_admin_resident_get"
     * })
     * @return null|string
     */
    public function getResidentPhoto(): ?string
    {
        if ($this->getImage() !== null) {
            return $this->getDownloadString();
        }

        return null;
    }

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Assessment\Assessment", mappedBy="resident")
     */
    private $assessments;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentAdmission", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentAdmissions;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentAllergen", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentAllergens;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentDiagnosis", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentDiagnoses;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentDiet", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentDiets;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentEvent", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentEvents;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentMedicalHistoryCondition", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $historyConditions;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentMedication", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentMedications;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentMedicationAllergy", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentMedicationAllergies;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentPhysician", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentPhysicians;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentRent", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentRents;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentResponsiblePerson", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentResponsiblePersons;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentHealthInsurance", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentHealthInsurances;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentDocument", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentDocuments;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentRentIncrease", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentRentIncreases;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\FacilityEvent", mappedBy="residents", cascade={"persist"})
     */
    protected $facilityEvents;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentLedger", mappedBy="resident", cascade={"remove", "persist"})
     */
    private $residentLedgers;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentExpenseItem", mappedBy="resident", cascade={"remove", "persist"})
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $residentExpenseItems;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentAwayDays", mappedBy="resident", cascade={"remove", "persist"})
     * @ORM\OrderBy({"start" = "ASC"})
     */
    private $residentAwayDays;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSpace()
    {
        return $this->space;
    }

    /**
     * @param mixed $space
     */
    public function setSpace($space): void
    {
        $this->space = $space;
    }

    /**
     * @return Salutation
     */
    public function getSalutation(): Salutation
    {
        return $this->salutation;
    }

    /**
     * @param Salutation $salutation
     */
    public function setSalutation(Salutation $salutation): void
    {
        $this->salutation = $salutation;
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
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     */
    public function setMiddleName($middleName): void
    {
        $this->middleName = $middleName;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday(): \DateTime
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $birthday
     */
    public function setBirthday($birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @return int
     */
    public function getGender(): int
    {
        return $this->gender;
    }

    /**
     * @param int $gender
     */
    public function setGender($gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getSsn()
    {
        return $this->ssn;
    }

    /**
     * @param mixed $ssn
     */
    public function setSsn($ssn): void
    {
        $this->ssn = $ssn;
    }

    /**
     * @return mixed
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param mixed $phones
     */
    public function setPhones($phones): void
    {
        $this->phones = $phones;
    }

    /**
     * @return Image|null
     */
    public function getImage(): ?Image
    {
        return $this->image;
    }

    /**
     * @param Image|null $image
     */
    public function setImage(?Image $image): void
    {
        $this->image = $image;
    }

    /**
     * @return mixed
     */
    public function getAssessments()
    {
        return $this->assessments;
    }

    /**
     * @param mixed $assessments
     */
    public function setAssessments($assessments): void
    {
        $this->assessments = $assessments;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentAdmissions(): ArrayCollection
    {
        return $this->residentAdmissions;
    }

    /**
     * @param ArrayCollection $residentAdmissions
     */
    public function setResidentAdmissions(ArrayCollection $residentAdmissions): void
    {
        $this->residentAdmissions = $residentAdmissions;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentAllergens(): ArrayCollection
    {
        return $this->residentAllergens;
    }

    /**
     * @param ArrayCollection $residentAllergens
     */
    public function setResidentAllergens(ArrayCollection $residentAllergens): void
    {
        $this->residentAllergens = $residentAllergens;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentDiagnoses(): ArrayCollection
    {
        return $this->residentDiagnoses;
    }

    /**
     * @param ArrayCollection $residentDiagnoses
     */
    public function setResidentDiagnoses(ArrayCollection $residentDiagnoses): void
    {
        $this->residentDiagnoses = $residentDiagnoses;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentDiets(): ArrayCollection
    {
        return $this->residentDiets;
    }

    /**
     * @param ArrayCollection $residentDiets
     */
    public function setResidentDiets(ArrayCollection $residentDiets): void
    {
        $this->residentDiets = $residentDiets;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentEvents(): ArrayCollection
    {
        return $this->residentEvents;
    }

    /**
     * @param ArrayCollection $residentEvents
     */
    public function setResidentEvents(ArrayCollection $residentEvents): void
    {
        $this->residentEvents = $residentEvents;
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
    public function getResidentMedications(): ArrayCollection
    {
        return $this->residentMedications;
    }

    /**
     * @param ArrayCollection $residentMedications
     */
    public function setResidentMedications(ArrayCollection $residentMedications): void
    {
        $this->residentMedications = $residentMedications;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentMedicationAllergies(): ArrayCollection
    {
        return $this->residentMedicationAllergies;
    }

    /**
     * @param ArrayCollection $residentMedicationAllergies
     */
    public function setResidentMedicationAllergies(ArrayCollection $residentMedicationAllergies): void
    {
        $this->residentMedicationAllergies = $residentMedicationAllergies;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentPhysicians(): ArrayCollection
    {
        return $this->residentPhysicians;
    }

    /**
     * @param ArrayCollection $residentPhysicians
     */
    public function setResidentPhysicians(ArrayCollection $residentPhysicians): void
    {
        $this->residentPhysicians = $residentPhysicians;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentRents(): ArrayCollection
    {
        return $this->residentRents;
    }

    /**
     * @param ArrayCollection $residentRents
     */
    public function setResidentRents(ArrayCollection $residentRents): void
    {
        $this->residentRents = $residentRents;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentResponsiblePersons(): ArrayCollection
    {
        return $this->residentResponsiblePersons;
    }

    /**
     * @param ArrayCollection $residentResponsiblePersons
     */
    public function setResidentResponsiblePersons(ArrayCollection $residentResponsiblePersons): void
    {
        $this->residentResponsiblePersons = $residentResponsiblePersons;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentHealthInsurances(): ArrayCollection
    {
        return $this->residentHealthInsurances;
    }

    /**
     * @param ArrayCollection $residentHealthInsurances
     */
    public function setResidentHealthInsurances(ArrayCollection $residentHealthInsurances): void
    {
        $this->residentHealthInsurances = $residentHealthInsurances;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentDocuments(): ArrayCollection
    {
        return $this->residentDocuments;
    }

    /**
     * @param ArrayCollection $residentDocuments
     */
    public function setResidentDocuments(ArrayCollection $residentDocuments): void
    {
        $this->residentDocuments = $residentDocuments;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentRentIncreases(): ArrayCollection
    {
        return $this->residentRentIncreases;
    }

    /**
     * @param ArrayCollection $residentRentIncreases
     */
    public function setResidentRentIncreases(ArrayCollection $residentRentIncreases): void
    {
        $this->residentRentIncreases = $residentRentIncreases;
    }

    /**
     * @return mixed
     */
    public function getFacilityEvents()
    {
        return $this->facilityEvents;
    }

    /**
     * @param mixed $facilityEvents
     */
    public function setFacilityEvents($facilityEvents): void
    {
        $this->facilityEvents = $facilityEvents;

        /** @var FacilityEvent $facilityEvent */
        foreach ($this->facilityEvents as $facilityEvent) {
            $facilityEvent->addResident($this);
        }
    }

    /**
     * @param FacilityEvent $facilityEvent
     */
    public function addFacilityEvent(FacilityEvent $facilityEvent): void
    {
        $facilityEvent->addResident($this);
        $this->facilityEvents[] = $facilityEvent;
    }

    /**
     * @param FacilityEvent $facilityEvent
     */
    public function removeFacilityEvent(FacilityEvent $facilityEvent): void
    {
        $this->facilityEvents->removeElement($facilityEvent);
        $facilityEvent->removeResident($this);
    }

    /**
     * @return null|string
     */
    public function getDownloadUrl(): ?string
    {
        return $this->downloadUrl;
    }

    /**
     * @param null|string $downloadUrl
     */
    public function setDownloadUrl(?string $downloadUrl): void
    {
        $this->downloadUrl = $downloadUrl;
    }

    /**
     * @return null|string
     */
    public function getDownloadString(): ?string
    {
        return $this->downloadString;
    }

    /**
     * @param null|string $downloadString
     */
    public function setDownloadString(?string $downloadString): void
    {
        $this->downloadString = $downloadString;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentLedgers(): ArrayCollection
    {
        return $this->residentLedgers;
    }

    /**
     * @param ArrayCollection $residentLedgers
     */
    public function setResidentLedgers(ArrayCollection $residentLedgers): void
    {
        $this->residentLedgers = $residentLedgers;
    }

    /**
     * @return mixed
     */
    public function getResidentExpenseItems()
    {
        return $this->residentExpenseItems;
    }

    /**
     * @param mixed $residentExpenseItems
     */
    public function setResidentExpenseItems($residentExpenseItems): void
    {
        $this->residentExpenseItems = $residentExpenseItems;
    }

    /**
     * @return mixed
     */
    public function getResidentAwayDays()
    {
        return $this->residentAwayDays;
    }

    /**
     * @param ArrayCollection $residentAwayDays
     */
    public function setResidentAwayDays(ArrayCollection $residentAwayDays): void
    {
        $this->residentAwayDays = $residentAwayDays;
    }
}
