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
 * Class FunnelStage
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\FunnelStageRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_lead_funnel_stage_add",
 *          "api_lead_funnel_stage_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_lead_funnel_stage")
 * @Grid(
 *     api_lead_funnel_stage_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "fs.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "fs.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "seq_no",
 *              "type"       = "string",
 *              "field"      = "fs.seqNo"
 *          },
 *          {
 *              "id"         = "open",
 *              "type"       = "boolean",
 *              "field"      = "fs.open"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class FunnelStage
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_funnel_stage_list",
     *     "api_lead_funnel_stage_get",
     *     "api_lead_lead_funnel_stage_list",
     *     "api_lead_lead_funnel_stage_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_lead_funnel_stage_add",
     *          "api_lead_funnel_stage_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_funnel_stage_add",
     *          "api_lead_funnel_stage_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=120)
     * @Groups({
     *     "api_lead_funnel_stage_list",
     *     "api_lead_funnel_stage_get",
     *     "api_lead_lead_funnel_stage_list",
     *     "api_lead_lead_funnel_stage_get"
     * })
     */
    private $title;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_lead_funnel_stage_add",
     *          "api_lead_funnel_stage_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 20,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_funnel_stage_add",
     *          "api_lead_funnel_stage_edit"
     *      }
     * )
     * @ORM\Column(name="seq_no", type="string", length=20)
     * @Groups({
     *     "api_lead_funnel_stage_list",
     *     "api_lead_funnel_stage_get"
     * })
     */
    private $seqNo;

    /**
     * @var bool
     * @ORM\Column(name="open", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_lead_funnel_stage_list",
     *     "api_lead_funnel_stage_get"
     * })
     */
    protected $open;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_lead_funnel_stage_add",
     *     "api_lead_funnel_stage_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="leadFunnelStages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_funnel_stage_list",
     *     "api_lead_funnel_stage_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\LeadFunnelStage", mappedBy="stage", cascade={"remove", "persist"})
     */
    private $leadFunnelStages;

    public function getId()
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
     * @return null|string
     */
    public function getSeqNo(): ?string
    {
        return $this->seqNo;
    }

    /**
     * @param null|string $seqNo
     */
    public function setSeqNo(?string $seqNo): void
    {
        $this->seqNo = $seqNo;
    }

    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->open;
    }

    /**
     * @param bool $open
     */
    public function setOpen(bool $open): void
    {
        $this->open = $open;
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
    public function getLeadFunnelStages(): ArrayCollection
    {
        return $this->leadFunnelStages;
    }

    /**
     * @param ArrayCollection $leadFunnelStages
     */
    public function setLeadFunnelStages(ArrayCollection $leadFunnelStages): void
    {
        $this->leadFunnelStages = $leadFunnelStages;
    }
}
