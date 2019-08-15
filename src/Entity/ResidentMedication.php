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
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rm.id"
 *          },
 *          {
 *              "id"         = "medication",
 *              "type"       = "string",
 *              "field"      = "m.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "form_factor",
 *              "type"       = "string",
 *              "field"      = "ff.title",
 *              "sortable"   = false
 *          },
 *          {
 *              "id"         = "dosage",
 *              "type"       = "string",
 *              "field"      = "CONCAT(rm.dosage, ' (', rm.dosageUnit, ')')",
 *              "sortable"   = false
 *          },
 *          {
 *              "id"         = "am",
 *              "type"       = "string",
 *              "field"      = "rm.am"
 *          },
 *          {
 *              "id"         = "nn",
 *              "type"       = "string",
 *              "field"      = "rm.nn"
 *          },
 *          {
 *              "id"         = "pm",
 *              "type"       = "string",
 *              "field"      = "rm.pm"
 *          },
 *          {
 *              "id"         = "hs",
 *              "type"       = "string",
 *              "field"      = "rm.hs"
 *          },
 *          {
 *              "id"         = "prn",
 *              "type"       = "boolean",
 *              "field"      = "rm.prn"
 *          },
 *          {
 *              "id"         = "discontinued",
 *              "type"       = "boolean",
 *              "field"      = "rm.discontinued"
 *          },
 *          {
 *              "id"         = "treatment",
 *              "type"       = "boolean",
 *              "field"      = "rm.treatment"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "rm.notes",
 *              "sortable"   = false
 *          },
 *          {
 *              "id"         = "prescription_number",
 *              "type"       = "string",
 *              "field"      = "rm.prescriptionNumber",
 *              "sortable"   = false
 *          },
 *          {
 *              "id"         = "hidden_discontinued",
 *              "type"       = "color_row",
 *              "hidden"     = true,
 *              "field"      = "rm.discontinued"
 *          }
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
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_medication_add",
     *     "api_admin_resident_medication_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentMedications")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $resident;

    /**
     * @var Physician
     * @Assert\NotNull(message = "Please select a Physician", groups={
     *     "api_admin_resident_medication_add",
     *     "api_admin_resident_medication_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Physician", inversedBy="residentMedications")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_physician", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $physician;

    /**
     * @var Medication
     * @Assert\NotNull(message = "Please select Medication", groups={
     *     "api_admin_resident_medication_add",
     *     "api_admin_resident_medication_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Medication", inversedBy="residentMedications")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_medication", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $medication;

    /**
     * @var MedicationFormFactor
     * @Assert\NotNull(message = "Please select a Medication Form Factor", groups={
     *     "api_admin_resident_medication_add",
     *     "api_admin_resident_medication_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\MedicationFormFactor", inversedBy="formFactors")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_form_factor", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $formFactor;

    /**
     * @var string $dosage
     * @ORM\Column(name="dosage", type="string", length=25)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_medication_add",
     *     "api_admin_resident_medication_edit"
     * })
     * @Assert\Regex(pattern= "/^[0-9\.\-\/]+$/",
     *     message="The value entered is not a valid type. Examples of valid entries: '2, 0.5, 10/15, 0.4-4'.",
     *     groups={
     *         "api_admin_resident_medication_add",
     *         "api_admin_resident_medication_edit"
     * })
     * @Assert\Length(
     *      max = 25,
     *      maxMessage = "Dosage cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_medication_add",
     *          "api_admin_resident_medication_edit"
     * })
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $dosage;

    /**
     * @var string $dosageUnit
     * @ORM\Column(name="dosage_unit", type="string", length=100)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_medication_add",
     *     "api_admin_resident_medication_edit"
     * })
     * @Assert\Regex(pattern= "/^[a-zA-Z0-9\%\+\/]+$/",
     *     message="The value entered is not a valid type. Allowed symbols are: '%, +, /'.",
     *     groups={
     *         "api_admin_resident_medication_add",
     *         "api_admin_resident_medication_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Dosage Unit cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_medication_add",
     *          "api_admin_resident_medication_edit"
     *},
     * )
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $dosageUnit;

    /**
     * @var string $prescriptionNumber
     * @ORM\Column(name="prescription_number", type="string", length=40, nullable=true)
     * @Assert\Length(
     *      max = 40,
     *      maxMessage = "Prescription Number cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_medication_add",
     *          "api_admin_resident_medication_edit"
     * })
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $prescriptionNumber;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Instructions cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_medication_add",
     *          "api_admin_resident_medication_edit"
     * })
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $notes;

    /**
     * @var string $am
     * @ORM\Column(name="medication_am", type="string", length=10, nullable=true)
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "AM cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_medication_add",
     *          "api_admin_resident_medication_edit"
     * })
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $am = 0;

    /**
     * @var string $nn
     * @ORM\Column(name="medication_nn", type="string", length=10, nullable=true)
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "NN cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_medication_add",
     *          "api_admin_resident_medication_edit"
     * })
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $nn = 0;

    /**
     * @var string $pm
     * @ORM\Column(name="medication_pm", type="string", length=10, nullable=true)
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "PM cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_medication_add",
     *          "api_admin_resident_medication_edit"
     * })
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $pm = 0;

    /**
     * @var string $hs
     * @ORM\Column(name="medication_hs", type="string", nullable=true)
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "HS cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_medication_add",
     *          "api_admin_resident_medication_edit"
     * })
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $hs = 0;

    /**
     * @var bool
     * @ORM\Column(name="medication_prn", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    protected $prn;

    /**
     * @var bool
     * @ORM\Column(name="medication_discontinued", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    protected $discontinued;

    /**
     * @var bool
     * @ORM\Column(name="medication_treatment", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_resident_medication_grid",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    protected $treatment;

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
     * @return Resident|null
     */
    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident|null $resident
     */
    public function setResident(?Resident $resident): void
    {
        $this->resident = $resident;
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
     */
    public function setPhysician(?Physician $physician): void
    {
        $this->physician = $physician;
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
     */
    public function setMedication(?Medication $medication): void
    {
        $this->medication = $medication;
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
     */
    public function setFormFactor(?MedicationFormFactor $formFactor): void
    {
        $this->formFactor = $formFactor;
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

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
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
