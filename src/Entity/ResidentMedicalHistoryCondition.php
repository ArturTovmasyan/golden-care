<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentMedicalHistoryCondition
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentMedicalHistoryConditionRepository")
 * @ORM\Table(name="tbl_resident_medical_history_condition")
 * @Grid(
 *     api_admin_resident_medical_history_condition_grid={
 *          {"id", "number", true, true, "rmhc.id"},
 *          {"condition", "string", true, true, "mhc.title"},
 *          {"date", "string", true, true, "rmhc.date"},
 *          {"notes", "string", true, true, "rmhc.notes"}
 *     }
 * )
 */
class ResidentMedicalHistoryCondition
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_medical_history_condition_grid",
     *     "api_admin_resident_medical_history_condition_list",
     *     "api_admin_resident_medical_history_condition_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={"api_admin_resident_medical_history_condition_add", "api_admin_resident_medical_history_condition_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_medical_history_condition_grid", "api_admin_resident_medical_history_condition_list", "api_admin_resident_medical_history_condition_get"})
     */
    private $resident;

    /**
     * @var MedicalHistoryCondition
     * @Assert\NotNull(message = "Please select a MedicalHistoryCondition", groups={"api_admin_resident_medical_history_condition_add", "api_admin_resident_medical_history_condition_edit"})
     * @Assert\Valid(groups={"api_admin_resident_medical_history_condition_add", "api_admin_resident_medical_history_condition_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\MedicalHistoryCondition", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_medical_history_condition", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_medical_history_condition_grid", "api_admin_resident_medical_history_condition_list", "api_admin_resident_medical_history_condition_get"})
     */
    private $condition;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_medical_history_condition_add", "api_admin_resident_medical_history_condition_edit"}
     * )
     * @Groups({"api_admin_resident_medical_history_condition_grid", "api_admin_resident_medical_history_condition_list", "api_admin_resident_medical_history_condition_get"})
     */
    private $notes;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={"api_admin_resident_medical_history_condition_add", "api_admin_resident_medical_history_condition_edit"})
     * @Assert\DateTime(groups={"api_admin_resident_medical_history_condition_add", "api_admin_resident_medical_history_condition_edit"})
     * @ORM\Column(name="date", type="datetime")
     * @Groups({"api_admin_resident_medical_history_condition_grid", "api_admin_resident_medical_history_condition_list", "api_admin_resident_medical_history_condition_get"})
     */
    private $date;

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
     * @return ResidentMedicalHistoryCondition
     */
    public function setResident(?Resident $resident): self
    {
        $this->resident = $resident;

        return $this;
    }

    /**
     * @return MedicalHistoryCondition|null
     */
    public function getCondition(): ?MedicalHistoryCondition
    {
        return $this->condition;
    }

    /**
     * @param MedicalHistoryCondition|null $condition
     * @return ResidentMedicalHistoryCondition
     */
    public function setCondition(?MedicalHistoryCondition $condition): self
    {
        $this->condition = $condition;

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

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }
}
