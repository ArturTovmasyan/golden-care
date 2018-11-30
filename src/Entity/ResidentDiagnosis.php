<?php

namespace App\Entity;

use App\Model\DiagnosisType;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentDiagnosis
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentDiagnosisRepository")
 * @ORM\Table(name="tbl_resident_diagnosis")
 * @Grid(
 *     api_admin_resident_diagnosis_grid={
 *          {"id", "number", true, true, "rd.id"},
 *          {"diagnosis", "string", true, true, "d.title"},
 *          {"type", "enum", true, true, "rd.type", {"\App\Model\DiagnosisType", "getTypeDefaultNames"}},
 *          {"notes", "string", true, true, "rd.notes"}
 *     }
 * )
 */
class ResidentDiagnosis
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_diagnosis_grid",
     *     "api_admin_resident_diagnosis_list",
     *     "api_admin_resident_diagnosis_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={"api_admin_resident_diagnosis_add", "api_admin_resident_diagnosis_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_diagnosis_grid", "api_admin_resident_diagnosis_list", "api_admin_resident_diagnosis_get"})
     */
    private $resident;

    /**
     * @var Diagnosis
     * @Assert\NotNull(message = "Please select a Diagnosis", groups={"api_admin_resident_diagnosis_add", "api_admin_resident_diagnosis_edit"})
     * @Assert\Valid(groups={"api_admin_resident_diagnosis_add", "api_admin_resident_diagnosis_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Diagnosis", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_diagnosis", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_diagnosis_grid", "api_admin_resident_diagnosis_list", "api_admin_resident_diagnosis_get"})
     */
    private $diagnosis;

    /**
     * @var int
     * @Assert\NotBlank(groups={"api_admin_resident_diagnosis_add", "api_admin_resident_diagnosis_edit"})
     * @Assert\Choice(
     *     callback={"App\Model\DiagnosisType","getTypeValues"},
     *     groups={"api_admin_resident_diagnosis_add", "api_admin_resident_diagnosis_edit"}
     * )
     * @ORM\Column(name="type", type="integer", length=1)
     * @Groups({"api_admin_resident_diagnosis_grid", "api_admin_resident_diagnosis_list", "api_admin_resident_diagnosis_get"})
     */
    private $type = DiagnosisType::PRIMARY;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_diagnosis_add", "api_admin_resident_diagnosis_edit"}
     * )
     * @Groups({"api_admin_resident_diagnosis_grid", "api_admin_resident_diagnosis_list", "api_admin_resident_diagnosis_get"})
     */
    private $notes;

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
     * @return ResidentDiagnosis
     */
    public function setResident(?Resident $resident): self
    {
        $this->resident = $resident;

        return $this;
    }

    /**
     * @return Diagnosis|null
     */
    public function getDiagnosis(): ?Diagnosis
    {
        return $this->diagnosis;
    }

    /**
     * @param Diagnosis|null $diagnosis
     * @return ResidentDiagnosis
     */
    public function setDiagnosis(?Diagnosis $diagnosis): self
    {
        $this->diagnosis = $diagnosis;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType($type): self
    {
        $this->type = $type;

        return $this;
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
}