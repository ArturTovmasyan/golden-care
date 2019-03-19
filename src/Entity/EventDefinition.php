<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class EventDefinition
 *
 * @ORM\Entity(repositoryClass="App\Repository\EventDefinitionRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space.",
 *     groups={
 *          "api_admin_event_definition_add",
 *          "api_admin_event_definition_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_event_definition")
 * @Grid(
 *     api_admin_event_definition_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ed.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "ed.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "ffc",
 *              "type"       = "boolean",
 *              "field"      = "ed.ffc"
 *          },
 *          {
 *              "id"         = "ihc",
 *              "type"       = "boolean",
 *              "field"      = "ed.ihc"
 *          },
 *          {
 *              "id"         = "il",
 *              "type"       = "boolean",
 *              "field"      = "ed.il"
 *          },
 *          {
 *              "id"         = "physician",
 *              "type"       = "boolean",
 *              "field"      = "ed.physician"
 *          },
 *          {
 *              "id"         = "responsible_person",
 *              "type"       = "boolean",
 *              "field"      = "ed.responsiblePerson"
 *          },
 *          {
 *              "id"         = "additional_date",
 *              "type"       = "boolean",
 *              "field"      = "ed.additionalDate"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class EventDefinition
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_event_definition_grid",
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_event_definition_add",
     *     "api_admin_event_definition_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_event_definition_add",
     *          "api_admin_event_definition_edit"
     * })
     * @ORM\Column(name="title", type="string", length=100)
     * @Groups({
     *     "api_admin_event_definition_grid",
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_event_definition_add",
     *     "api_admin_event_definition_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_event_definition_grid",
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    private $space;

    /**
     * @var bool
     * @ORM\Column(name="show_resident_ffc", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_grid",
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $ffc;

    /**
     * @var bool
     * @ORM\Column(name="show_resident_ihc", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_grid",
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $ihc;

    /**
     * @var bool
     * @ORM\Column(name="show_resident_il", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_grid",
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $il;

    /**
     * @var bool
     * @ORM\Column(name="show_physician", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_grid",
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $physician;

    /**
     * @var bool
     * @ORM\Column(name="show_responsible_person", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_grid",
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $responsiblePerson;

    /**
     * @var bool
     * @ORM\Column(name="show_additional_date", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_event_definition_grid",
     *     "api_admin_event_definition_list",
     *     "api_admin_event_definition_get"
     * })
     */
    protected $additionalDate;

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
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = preg_replace('/\s\s+/', ' ', $title);
    }

    /**
     * @return Space|null
     */
    public function getSpace(): ?Space
    {
        return $this->space;
    }

    /**
     * @param Space|null $space
     * @return EventDefinition
     */
    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return bool
     */
    public function isFfc(): bool
    {
        return $this->ffc;
    }

    /**
     * @param bool $ffc
     */
    public function setFfc(bool $ffc): void
    {
        $this->ffc = $ffc;
    }

    /**
     * @return bool
     */
    public function isIhc(): bool
    {
        return $this->ihc;
    }

    /**
     * @param bool $ihc
     */
    public function setIhc(bool $ihc): void
    {
        $this->ihc = $ihc;
    }

    /**
     * @return bool
     */
    public function isIl(): bool
    {
        return $this->il;
    }

    /**
     * @param bool $il
     */
    public function setIl(bool $il): void
    {
        $this->il = $il;
    }

    /**
     * @return bool
     */
    public function isPhysician(): bool
    {
        return $this->physician;
    }

    /**
     * @param bool $physician
     */
    public function setPhysician(bool $physician): void
    {
        $this->physician = $physician;
    }

    /**
     * @return bool
     */
    public function isResponsiblePerson(): bool
    {
        return $this->responsiblePerson;
    }

    /**
     * @param bool $responsiblePerson
     */
    public function setResponsiblePerson(bool $responsiblePerson): void
    {
        $this->responsiblePerson = $responsiblePerson;
    }

    /**
     * @return bool
     */
    public function isAdditionalDate(): bool
    {
        return $this->additionalDate;
    }

    /**
     * @param bool $additionalDate
     */
    public function setAdditionalDate(bool $additionalDate): void
    {
        $this->additionalDate = $additionalDate;
    }
}
