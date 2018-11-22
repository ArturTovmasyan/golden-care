<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentMedication
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentMedicationRepository")
 * @ORM\Table(name="tbl_resident_medication")
 * @Grid(
 *     api_admin_resident_medication_grid={
 *          {"id", "number", true, true, "rm.id"},
 *          {"medication", "string", true, true, "m.name"},
 *          {"form_factor", "string", true, true, "ff.title"},
 *          {"dosage", "string", true, true, "rm.dosage"},
 *          {"dosage_unit", "string", true, true, "rm.dosageUnit"},
 *          {"am", "string", true, true, "rm.am"},
 *          {"nn", "string", true, true, "rm.nn"},
 *          {"pm", "string", true, true, "rm.pm"},
 *          {"hs", "string", true, true, "rm.hs"},
 *          {"prn", "enum", true, true, "rm.prn", {"\App\Model\Boolean", "defaultValues"}},
 *          {"discontinued", "enum", true, true, "rm.discontinued", {"\App\Model\Boolean", "defaultValues"}},
 *          {"treatment", "enum", true, true, "rm.treatment", {"\App\Model\Boolean", "defaultValues"}},
 *          {"notes", "string", true, true, "rm.notes"},
 *          {"prescription_number", "string", true, true, "rm.prescriptionNumber"},
 *     }
 * )
 */
class ResidentMedication
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    private $resident;

    /**
     * @var Physician
     * @Assert\NotNull(message = "Please select a Physician", groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Physician")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_physician", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    private $physician;

    /**
     * @var Medication
     * @Assert\NotNull(message = "Please select a Medication", groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Medication")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_medication", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    private $medication;

    /**
     * @var MedicationFormFactor
     * @Assert\NotNull(message = "Please select a MedicationFormFactor", groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\MedicationFormFactor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_form_factor", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    private $formFactor;

    /**
     * @var string $dosage
     * @ORM\Column(name="dosage", type="string", length=10)
     * @Assert\NotBlank(groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"})
     * @Assert\Regex(pattern= "/[^0-9\.\-\/]/",
     *     match=false,
     *     message="The value {{ value }} is not a valid type. Try to add something like '2, 0.5, 10/15, 0.4-4'",
     *     groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"}
     * )
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Dosage cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"}
     * )
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    private $dosage;

    /**
     * @var string $dosageUnit
     * @ORM\Column(name="dosage_unit", type="string", length=20)
     * @Assert\NotBlank(groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"})
     * @Assert\Regex(pattern= "/[^a-zA-Z0-9\%\+\/]/",
     *     match=false,
     *     message="The value {{ value }} is not a valid type. Available symbols are: '%, +, /'",
     *     groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"}
     * )
     * @Assert\Length(
     *      max = 20,
     *      maxMessage = "Dosage unit cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"},
     * )
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    private $dosageUnit;

    /**
     * @var string $prescriptionNumber
     * @ORM\Column(name="prescription_number", type="string", length=40, nullable=true)
     * @Assert\Length(
     *      max = 40,
     *      maxMessage = "Prescription number cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"}
     * )
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    private $prescriptionNumber;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"}
     * )
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    private $notes;

    /**
     * @var string $am
     * @ORM\Column(name="medication_am", type="string", length=10, nullable=true)
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Am cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"}
     * )
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    private $am = 0;

    /**
     * @var string $nn
     * @ORM\Column(name="medication_nn", type="string", length=10, nullable=true)
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Nn cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"}
     * )
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    private $nn = 0;

    /**
     * @var string $pm
     * @ORM\Column(name="medication_pm", type="string", length=10, nullable=true)
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Pm cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"}
     * )
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    private $pm = 0;

    /**
     * @var string $hs
     * @ORM\Column(name="medication_hs", type="string", nullable=true)
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Hs cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_medication_add", "api_admin_resident_medication_edit"}
     * )
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    private $hs = 0;

    /**
     * @var bool
     * @ORM\Column(name="medication_prn", type="boolean", options={"default" = 0})
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    protected $prn;

    /**
     * @var bool
     * @ORM\Column(name="medication_discontinued", type="boolean", options={"default" = 0})
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    protected $discontinued;

    /**
     * @var bool
     * @ORM\Column(name="medication_treatment", type="boolean", options={"default" = 0})
     * @Groups({"api_admin_resident_medication_grid", "api_admin_resident_medication_list", "api_admin_resident_medication_get"})
     */
    protected $treatment;

    /**
     * @return int
     */
    public function getId(): int
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
     * @return Resident|null
     */
    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident|null $resident
     * @return ResidentMedication
     */
    public function setResident(?Resident $resident): self
    {
        $this->resident = $resident;

        return $this;
    }

    /**
     * @return Physician|null
     */
    public function getPhysician(): ?Physician
    {
        return $this->physician;
    }

    /**
     * @param Physician|null $physician
     * @return ResidentMedication
     */
    public function setPhysician(?Physician $physician): self
    {
        $this->physician = $physician;

        return $this;
    }

    /**
     * @return Medication|null
     */
    public function getMedication(): ?Medication
    {
        return $this->medication;
    }

    /**
     * @param Medication|null $medication
     * @return ResidentMedication
     */
    public function setMedication(?Medication $medication): self
    {
        $this->medication = $medication;

        return $this;
    }

    /**
     * @return MedicationFormFactor|null
     */
    public function getFormFactor(): ?MedicationFormFactor
    {
        return $this->formFactor;
    }

    /**
     * @param MedicationFormFactor|null $formFactor
     * @return ResidentMedication
     */
    public function setFormFactor(?MedicationFormFactor $formFactor): self
    {
        $this->formFactor = $formFactor;

        return $this;
    }

    /**
     * @return string
     */
    public function getDosage(): string
    {
        return $this->dosage;
    }

    /**
     * @param string $dosage
     */
    public function setDosage(string $dosage): void
    {
        $this->dosage = $dosage;
    }

    /**
     * @return string
     */
    public function getDosageUnit(): string
    {
        return $this->dosageUnit;
    }

    /**
     * @param string $dosageUnit
     */
    public function setDosageUnit(string $dosageUnit): void
    {
        $this->dosageUnit = $dosageUnit;
    }

    /**
     * @return string
     */
    public function getPrescriptionNumber(): string
    {
        return $this->prescriptionNumber;
    }

    /**
     * @param string $prescriptionNumber
     */
    public function setPrescriptionNumber(string $prescriptionNumber): void
    {
        $this->prescriptionNumber = $prescriptionNumber;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return string
     */
    public function getAm(): string
    {
        return $this->am;
    }

    /**
     * @param string $am
     */
    public function setAm(string $am): void
    {
        $this->am = $am;
    }

    /**
     * @return string
     */
    public function getNn(): string
    {
        return $this->nn;
    }

    /**
     * @param string $nn
     */
    public function setNn(string $nn): void
    {
        $this->nn = $nn;
    }

    /**
     * @return string
     */
    public function getPm(): string
    {
        return $this->pm;
    }

    /**
     * @param string $pm
     */
    public function setPm(string $pm): void
    {
        $this->pm = $pm;
    }

    /**
     * @return string
     */
    public function getHs(): string
    {
        return $this->hs;
    }

    /**
     * @param string $hs
     */
    public function setHs(string $hs): void
    {
        $this->hs = $hs;
    }

    /**
     * @return bool
     */
    public function isPrn(): bool
    {
        return $this->prn;
    }

    /**
     * @param bool $prn
     */
    public function setPrn(bool $prn): void
    {
        $this->prn = $prn;
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

    /**
     * @return bool
     */
    public function isTreatment(): bool
    {
        return $this->treatment;
    }

    /**
     * @param bool $treatment
     */
    public function setTreatment(bool $treatment): void
    {
        $this->treatment = $treatment;
    }
}
