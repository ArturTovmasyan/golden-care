<?php

namespace App\Entity\Lead;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class LeadFunnelStage
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\LeadFunnelStageRepository")
 * @ORM\Table(name="tbl_lead_lead_funnel_stage")
 * @Grid(
 *     api_lead_lead_funnel_stage_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "lfs.id"
 *          },
 *          {
 *              "id"         = "stage",
 *              "type"       = "string",
 *              "field"      = "fs.title"
 *          },
 *          {
 *              "id"         = "reason",
 *              "type"       = "string",
 *              "field"      = "scr.title"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "lfs.date"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(lfs.notes, 1, 100)), CASE WHEN LENGTH(lfs.notes) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "created_by",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(u.firstName, ''), ' ', COALESCE(u.lastName, ''))"
 *          }
 *     }
 * )
 */
class LeadFunnelStage
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_lead_funnel_stage_list",
     *     "api_lead_lead_funnel_stage_get"
     * })
     */
    private $id;

    /**
     * @var Lead
     * @Assert\NotNull(message = "Please select a Lead", groups={
     *          "api_lead_lead_funnel_stage_add",
     *          "api_lead_lead_funnel_stage_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Lead", inversedBy="leadFunnelStages", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_lead", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_lead_funnel_stage_list",
     *     "api_lead_lead_funnel_stage_get"
     * })
     */
    private $lead;

    /**
     * @var FunnelStage
     * @Assert\NotNull(message = "Please select a Funnel Stage", groups={
     *          "api_lead_lead_funnel_stage_add",
     *          "api_lead_lead_funnel_stage_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\FunnelStage", inversedBy="leadFunnelStages", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_funnel_stage", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_lead_funnel_stage_list",
     *     "api_lead_lead_funnel_stage_get"
     * })
     */
    private $stage;

    /**
     * @var StageChangeReason
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\StageChangeReason", inversedBy="leadFunnelStages", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_stage_change_reason", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_lead_funnel_stage_list",
     *     "api_lead_lead_funnel_stage_get"
     * })
     */
    private $reason;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *          "api_lead_lead_funnel_stage_add",
     *          "api_lead_lead_funnel_stage_edit"
     * })
     * @Assert\DateTime(groups={
     *          "api_lead_lead_funnel_stage_add",
     *          "api_lead_lead_funnel_stage_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_lead_lead_funnel_stage_list",
     *     "api_lead_lead_funnel_stage_get"
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
     *          "api_lead_lead_funnel_stage_add",
     *          "api_lead_lead_funnel_stage_edit"
     * })
     * @Groups({
     *     "api_lead_lead_funnel_stage_list",
     *     "api_lead_lead_funnel_stage_get"
     * })
     */
    private $notes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Lead|null
     */
    public function getLead(): ?Lead
    {
        return $this->lead;
    }

    /**
     * @param Lead|null $lead
     */
    public function setLead(?Lead $lead): void
    {
        $this->lead = $lead;
    }

    /**
     * @return FunnelStage|null
     */
    public function getStage(): ?FunnelStage
    {
        return $this->stage;
    }

    /**
     * @param FunnelStage|null $stage
     */
    public function setStage(?FunnelStage $stage): void
    {
        $this->stage = $stage;
    }

    /**
     * @return StageChangeReason|null
     */
    public function getReason(): ?StageChangeReason
    {
        return $this->reason;
    }

    /**
     * @param StageChangeReason|null $reason
     */
    public function setReason(?StageChangeReason $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return \DateTime|null
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime|null $date
     */
    public function setDate(?\DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return null|string
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param null|string $notes
     */
    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }
}
