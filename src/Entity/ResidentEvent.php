<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentEvent
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentEventRepository")
 * @ORM\Table(name="tbl_resident_event")
 * @Grid(
 *     api_admin_resident_event_grid={
 *          {"id", "number", true, true, "re.id"},
 *          {"definition", "string", true, true, "ed.title"},
 *          {"date", "string", true, true, "re.date"},
 *          {"notes", "string", true, true, "re.notes"},
 *          {"physician", "string", true, true, "CONCAT(ps.title, ' ', p.firstName, ' ', p.lastName)"},
 *          {"responsible_person", "string", true, true, "CONCAT(rps.title, ' ', rp.firstName, ' ', rp.lastName)"},
 *          {"additional_date", "string", true, true, "re.additionalDate"},
 *     }
 * )
 */
class ResidentEvent
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_event_grid",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $id;

    /**
     * @var EventDefinition
     * @Assert\NotNull(message = "Please select a Definition", groups={"api_admin_resident_event_add"})
     * @ORM\ManyToOne(targetEntity="App\Entity\EventDefinition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_definition", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_event_grid", "api_admin_resident_event_list", "api_admin_resident_event_get"})
     */
    private $definition;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={"api_admin_resident_event_add", "api_admin_resident_event_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_event_grid", "api_admin_resident_event_list", "api_admin_resident_event_get"})
     */
    private $resident;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={"api_admin_resident_event_add", "api_admin_resident_event_edit"})
     * @Assert\DateTime(groups={"api_admin_resident_event_add", "api_admin_resident_event_edit"})
     * @ORM\Column(name="date", type="datetime")
     * @Groups({"api_admin_resident_event_grid", "api_admin_resident_event_list", "api_admin_resident_event_get"})
     */
    private $date;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_event_add", "api_admin_resident_event_edit"}
     * )
     * @Groups({"api_admin_resident_event_grid", "api_admin_resident_event_list", "api_admin_resident_event_get"})
     */
    private $notes;

    /**
     * @var Physician
     * @ORM\ManyToOne(targetEntity="App\Entity\Physician")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_physician", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({"api_admin_resident_event_grid", "api_admin_resident_event_list", "api_admin_resident_event_get"})
     */
    private $physician;

    /**
     * @var ResponsiblePerson
     * @ORM\ManyToOne(targetEntity="App\Entity\ResponsiblePerson")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_responsible_person", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({"api_admin_resident_event_grid", "api_admin_resident_event_list", "api_admin_resident_event_get"})
     */
    private $responsiblePerson;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={"api_admin_resident_event_add", "api_admin_resident_event_edit"})
     * @ORM\Column(name="additional_date", type="datetime", nullable=true)
     * @Groups({"api_admin_resident_event_grid", "api_admin_resident_event_list", "api_admin_resident_event_get"})
     */
    private $additionalDate;

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
     * @return EventDefinition|null
     */
    public function getDefinition(): ?EventDefinition
    {
        return $this->definition;
    }

    /**
     * @param EventDefinition|null $definition
     * @return ResidentEvent
     */
    public function setDefinition(?EventDefinition $definition): self
    {
        $this->definition = $definition;

        return $this;
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
     * @return ResidentEvent
     */
    public function setResident(?Resident $resident): self
    {
        $this->resident = $resident;

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
     * @return Physician|null
     */
    public function getPhysician(): ?Physician
    {
        return $this->physician;
    }

    /**
     * @param Physician|null $physician
     * @return ResidentEvent
     */
    public function setPhysician(?Physician $physician): self
    {
        $this->physician = $physician;

        return $this;
    }

    /**
     * @return ResponsiblePerson|null
     */
    public function getResponsiblePerson(): ?ResponsiblePerson
    {
        return $this->responsiblePerson;
    }

    /**
     * @param ResponsiblePerson|null $responsiblePerson
     * @return ResidentEvent
     */
    public function setResponsiblePerson(?ResponsiblePerson $responsiblePerson): self
    {
        $this->responsiblePerson = $responsiblePerson;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getAdditionalDate(): \DateTime
    {
        return $this->additionalDate;
    }

    /**
     * @param \DateTime $additionalDate
     */
    public function setAdditionalDate($additionalDate): void
    {
        $this->additionalDate = $additionalDate;
    }
}
