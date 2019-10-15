<?php

namespace App\Entity\Lead;

use App\Entity\Facility;
use App\Entity\User;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class Activity
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\ActivityRepository")
 * @ORM\Table(name="tbl_lead_activity")
 * @Grid(
 *     api_lead_activity_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "a.id"
 *          },
 *          {
 *              "id"         = "owner_type",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "a.ownerType"
 *          },
 *          {
 *              "id"         = "lead_id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "l.id"
 *          },
 *          {
 *              "id"         = "organization_id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "o.id"
 *          },
 *          {
 *              "id"         = "referral_id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "r.id"
 *          },
 *          {
 *              "id"         = "outreach_id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ou.id"
 *          },
 *          {
 *              "id"         = "activity",
 *              "type"       = "string",
 *              "field"      = "a.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "date_entered",
 *              "type"       = "date",
 *              "field"      = "a.date"
 *          },
 *          {
 *              "id"         = "entered_by",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(cb.firstName, ''), ' ', COALESCE(cb.lastName, ''))"
 *          },
 *          {
 *              "id"         = "task_owner",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(u.firstName, ''), ' ', COALESCE(u.lastName, ''))"
 *          },
 *          {
 *              "id"         = "due_date",
 *              "type"       = "date",
 *              "field"      = "a.dueDate"
 *          },
 *          {
 *              "id"         = "status",
 *              "type"       = "string",
 *              "field"      = "st.title"
 *          },
 *          {
 *              "id"         = "type_info",
 *              "type"       = "string",
 *              "field"      = "(CASE WHEN a.ownerType=1 THEN CONCAT('Lead: ', l.firstName, ' ', l.lastName) WHEN a.ownerType=2 AND rc.id IS NOT NULL THEN CONCAT('Referral: ', rc.firstName, ' ', rc.lastName) WHEN a.ownerType=2 AND rc.id IS NULL THEN CONCAT('Referral: ', ro.name) WHEN a.ownerType=3 THEN CONCAT('Organization: ', o.name) WHEN a.ownerType=4 AND ouc.id IS NOT NULL THEN CONCAT('Outreach: ', ouc.firstName, ' ', ouc.lastName) WHEN a.ownerType=4 AND ouc.id IS NULL THEN 'Outreach' ELSE '' END)",
 *              "link"       = "owner_type:</lead/lead/:lead_id|/lead/referral/:referral_id|/lead/referral/organization/:organization_id|/lead/outreach/:outreach_id>"
 *          }
 *     }
 * )
 */
class Activity
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_outreach_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit",
     *          "api_lead_outreach_activity_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_outreach_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit",
     *          "api_lead_outreach_activity_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=100)
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $title;

    /**
     * @var int
     * @ORM\Column(name="owner_type", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\Lead\ActivityOwnerType","getTypeValues"},
     *     groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_outreach_activity_add"
     *     }
     * )
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $ownerType;

    /**
     * @var ActivityType
     * @Assert\NotNull(message = "Please select a Type", groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_outreach_activity_add"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\ActivityType", inversedBy="activities", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_type", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $type;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_outreach_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit",
     *          "api_lead_outreach_activity_edit"
     * })
     * @Assert\DateTime(groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_outreach_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit",
     *          "api_lead_outreach_activity_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
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
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_outreach_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit",
     *          "api_lead_outreach_activity_edit"
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $notes;

    /**
     * @var ActivityStatus
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\ActivityStatus", inversedBy="activities", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_status", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $status;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="leadActivities", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="assign_to", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $assignTo;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_outreach_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit",
     *          "api_lead_outreach_activity_edit"
     * })
     * @ORM\Column(name="due_date", type="datetime", nullable=true)
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $dueDate;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_referral_activity_add",
     *          "api_lead_organization_activity_add",
     *          "api_lead_outreach_activity_add",
     *          "api_lead_lead_activity_edit",
     *          "api_lead_referral_activity_edit",
     *          "api_lead_organization_activity_edit",
     *          "api_lead_outreach_activity_edit"
     * })
     * @ORM\Column(name="reminder_date", type="datetime", nullable=true)
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $reminderDate;

    /**
     * @var Facility
     * @ORM\ManyToOne(targetEntity="App\Entity\Facility", inversedBy="leadActivities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $facility;

    /**
     * @var Lead
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Lead", inversedBy="activities")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_lead", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Lead", groups={
     *          "api_lead_lead_activity_add",
     *          "api_lead_lead_activity_edit"
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $lead;

    /**
     * @var Referral
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Referral", inversedBy="activities")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_referral", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Referral", groups={
     *          "api_lead_referral_activity_add",
     *          "api_lead_referral_activity_edit"
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $referral;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Organization", inversedBy="activities")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_organization", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select an Organization", groups={
     *          "api_lead_organization_activity_add",
     *          "api_lead_organization_activity_edit"
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $organization;

    /**
     * @var Outreach
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Outreach", inversedBy="activities")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_outreach", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select an Outreach", groups={
     *          "api_lead_outreach_activity_add",
     *          "api_lead_outreach_activity_edit"
     * })
     * @Groups({
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $outreach;

    public function getId()
    {
        return $this->id;
    }

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
        $title = preg_replace('/\s\s+/', ' ', $title);
        $this->title = $title;
    }

    /**
     * @return int|null
     */
    public function getOwnerType(): ?int
    {
        return $this->ownerType;
    }

    /**
     * @param int|null $ownerType
     */
    public function setOwnerType(?int $ownerType): void
    {
        $this->ownerType = $ownerType;
    }

    /**
     * @return ActivityType|null
     */
    public function getType(): ?ActivityType
    {
        return $this->type;
    }

    /**
     * @param ActivityType|null $type
     */
    public function setType(?ActivityType $type): void
    {
        $this->type = $type;
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

    /**
     * @return ActivityStatus|null
     */
    public function getStatus(): ?ActivityStatus
    {
        return $this->status;
    }

    /**
     * @param ActivityStatus|null $status
     */
    public function setStatus(?ActivityStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return User|null
     */
    public function getAssignTo(): ?User
    {
        return $this->assignTo;
    }

    /**
     * @param User|null $assignTo
     */
    public function setAssignTo(?User $assignTo): void
    {
        $this->assignTo = $assignTo;
    }

    /**
     * @return \DateTime|null
     */
    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    /**
     * @param \DateTime|null $dueDate
     */
    public function setDueDate(?\DateTime $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getReminderDate(): ?\DateTime
    {
        return $this->reminderDate;
    }

    /**
     * @param \DateTime|null $reminderDate
     */
    public function setReminderDate(?\DateTime $reminderDate): void
    {
        $this->reminderDate = $reminderDate;
    }

    /**
     * @return Facility|null
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * @param Facility|null $facility
     */
    public function setFacility(?Facility $facility): void
    {
        $this->facility = $facility;
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
     * @return Referral|null
     */
    public function getReferral(): ?Referral
    {
        return $this->referral;
    }

    /**
     * @param Referral|null $referral
     */
    public function setReferral(?Referral $referral): void
    {
        $this->referral = $referral;
    }

    /**
     * @return Organization|null
     */
    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    /**
     * @param Organization|null $organization
     */
    public function setOrganization(?Organization $organization): void
    {
        $this->organization = $organization;
    }

    /**
     * @return Outreach|null
     */
    public function getOutreach(): ?Outreach
    {
        return $this->outreach;
    }

    /**
     * @param Outreach|null $outreach
     */
    public function setOutreach(?Outreach $outreach): void
    {
        $this->outreach = $outreach;
    }
}
