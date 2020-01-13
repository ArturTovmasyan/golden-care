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
use App\Annotation\Grid;

/**
 * Class OutreachType
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\OutreachTypeRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_lead_outreach_type_add",
 *          "api_lead_outreach_type_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_lead_outreach_type")
 * @Grid(
 *     api_lead_outreach_type_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ot.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "ot.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class OutreachType
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_outreach_type_list",
     *     "api_lead_outreach_type_get",
     *     "api_lead_outreach_list",
     *     "api_lead_outreach_get",
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_lead_outreach_type_add",
     *          "api_lead_outreach_type_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_outreach_type_add",
     *          "api_lead_outreach_type_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=120)
     * @Groups({
     *     "api_lead_outreach_type_list",
     *     "api_lead_outreach_type_get",
     *     "api_lead_outreach_list",
     *     "api_lead_outreach_get",
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_lead_outreach_type_add",
     *     "api_lead_outreach_type_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="leadOutreachTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_outreach_type_list",
     *     "api_lead_outreach_type_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Outreach", mappedBy="type", cascade={"remove", "persist"})
     */
    private $outreaches;

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
    public function getOutreaches(): ArrayCollection
    {
        return $this->outreaches;
    }

    /**
     * @param ArrayCollection $outreaches
     */
    public function setOutreaches(ArrayCollection $outreaches): void
    {
        $this->outreaches = $outreaches;
    }
}
