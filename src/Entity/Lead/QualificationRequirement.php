<?php

namespace App\Entity\Lead;

use App\Entity\Space;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use App\Annotation\Grid;

/**
 * Class QualificationRequirement
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\QualificationRequirementRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_lead_qualification_requirement_add",
 *          "api_lead_qualification_requirement_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_lead_qualification_requirement")
 * @Grid(
 *     api_lead_qualification_requirement_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "qr.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "qr.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "use",
 *              "type"       = "boolean",
 *              "field"      = "qr.canUse",
 *              "width"      = "3rem"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class QualificationRequirement
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_qualification_requirement_list",
     *     "api_lead_qualification_requirement_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_lead_qualification_requirement_add",
     *          "api_lead_qualification_requirement_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_qualification_requirement_add",
     *          "api_lead_qualification_requirement_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=120)
     * @Groups({
     *     "api_lead_qualification_requirement_list",
     *     "api_lead_qualification_requirement_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $title;

    /**
     * @var bool
     * @ORM\Column(name="can_use", type="boolean")
     */
    protected $canUse;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_lead_qualification_requirement_add",
     *     "api_lead_qualification_requirement_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="leadQualificationRequirements")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_qualification_requirement_list",
     *     "api_lead_qualification_requirement_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\LeadQualificationRequirement", mappedBy="qualificationRequirement", cascade={"remove", "persist"})
     */
    private $leadQualificationRequirements;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("use")
     * @Serializer\Groups({
     *     "api_lead_qualification_requirement_list",
     *     "api_lead_qualification_requirement_get"
     * })
     */
    public function getUse(): bool
    {
        return $this->isCanUse();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $title = preg_replace('/\s\s+/', ' ', $title);
        $this->title = $title;
    }

    /**
     * @return bool
     */
    public function isCanUse(): bool
    {
        return $this->canUse;
    }

    /**
     * @param bool $canUse
     */
    public function setCanUse(bool $canUse): void
    {
        $this->canUse = $canUse;
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
     */
    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return ArrayCollection
     */
    public function getLeadQualificationRequirements(): ArrayCollection
    {
        return $this->leadQualificationRequirements;
    }

    /**
     * @param ArrayCollection $leadQualificationRequirements
     */
    public function setLeadQualificationRequirements(ArrayCollection $leadQualificationRequirements): void
    {
        $this->leadQualificationRequirements = $leadQualificationRequirements;
    }
}
