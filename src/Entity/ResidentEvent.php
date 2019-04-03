<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\Collection;
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
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "re.id"
 *          },
 *          {
 *              "id"         = "definition",
 *              "type"       = "string",
 *              "field"      = "ed.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "re.date"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "re.notes"
 *          },
 *          {
 *              "id"         = "info",
 *              "sortable"   = false,
 *              "type"       = "json",
 *              "field"      = "info"
 *          }
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
     * @Assert\NotNull(message = "Please select a Definition", groups={
     *     "api_admin_resident_event_add"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\EventDefinition", inversedBy="residentEvents")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_definition", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_event_grid",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $definition;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_event_add",
     *     "api_admin_resident_event_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentEvents")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_event_grid",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $resident;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_event_add",
     *     "api_admin_resident_event_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_resident_event_add",
     *     "api_admin_resident_event_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_admin_resident_event_grid",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $date;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_event_add",
     *          "api_admin_resident_event_edit"
     * })
     * @Groups({
     *     "api_admin_resident_event_grid",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $notes;

    /**
     * @var Physician
     * @ORM\ManyToOne(targetEntity="App\Entity\Physician", inversedBy="residentEvents")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_physician", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_event_grid",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $physician;

//    /**
//     * @var ResponsiblePerson
//     * @ORM\ManyToOne(targetEntity="App\Entity\ResponsiblePerson", inversedBy="residentEvents")
//     * @ORM\JoinColumns({
//     *   @ORM\JoinColumn(name="id_responsible_person", referencedColumnName="id", onDelete="CASCADE")
//     * })
//     * @Groups({
//     *     "api_admin_resident_event_grid",
//     *     "api_admin_resident_event_list",
//     *     "api_admin_resident_event_get"
//     * })
//     */
//    private $responsiblePerson;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="App\Entity\ResponsiblePerson", mappedBy="residentEvents", cascade={"persist"})
     * @Groups({
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    protected $responsiblePersons;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *     "api_admin_resident_event_add",
     *     "api_admin_resident_event_edit"
     * })
     * @ORM\Column(name="additional_date", type="datetime", nullable=true)
     * @Groups({
     *     "api_admin_resident_event_grid",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $additionalDate;

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
     * @return EventDefinition|null
     */
    public function getDefinition(): ?EventDefinition
    {
        return $this->definition;
    }

    /**
     * @param EventDefinition|null $definition
     */
    public function setDefinition(?EventDefinition $definition): void
    {
        $this->definition = $definition;
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

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
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
     * @return mixed
     */
    public function getResponsiblePersons()
    {
        return $this->responsiblePersons;
    }

    /**
     * @param mixed $responsiblePersons
     */
    public function setResponsiblePersons($responsiblePersons): void
    {
        $this->responsiblePersons = $responsiblePersons;

        /** @var ResponsiblePerson $responsiblePerson */
        foreach ($this->responsiblePersons as $responsiblePerson) {
            $responsiblePerson->addResidentEvent($this);
        }
    }

    /**
     * @param ResponsiblePerson $responsiblePerson
     */
    public function addResponsiblePerson(ResponsiblePerson $responsiblePerson)
    {
        $responsiblePerson->addResidentEvent($this);
        $this->responsiblePersons[] = $responsiblePerson;
    }

    /**
     * @param ResponsiblePerson $responsiblePerson
     */
    public function removeResponsiblePerson(ResponsiblePerson $responsiblePerson)
    {
        $this->responsiblePersons->removeElement($responsiblePerson);
        $responsiblePerson->removeResidentEvent($this);
    }

//    /**
//     * @return ResponsiblePerson|null
//     */
//    public function getResponsiblePerson(): ?ResponsiblePerson
//    {
//        return $this->responsiblePerson;
//    }
//
//    /**
//     * @param ResponsiblePerson|null $responsiblePerson
//     */
//    public function setResponsiblePerson(?ResponsiblePerson $responsiblePerson): void
//    {
//        $this->responsiblePerson = $responsiblePerson;
//    }

    /**
     * @return \DateTime
     */
    public function getAdditionalDate(): ?\DateTime
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
