<?php

namespace App\Entity\Lead;

use App\Entity\User;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_lead_outreach")
 * @ORM\Entity(repositoryClass="App\Repository\Lead\OutreachRepository")
 * @Grid(
 *     api_lead_outreach_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ou.id"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "ou.date"
 *          },
 *          {
 *              "id"         = "organization_id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "o.id"
 *          },
 *          {
 *              "id"         = "contact",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(c.firstName, ''), ' ', COALESCE(c.lastName, ''))",
 *              "link"       = "/lead/outreach/:id"
 *          },
 *          {
 *              "id"         = "type",
 *              "type"       = "string",
 *              "field"      = "ot.title"
 *          },
 *          {
 *              "id"         = "organization",
 *              "type"       = "string",
 *              "field"      = "o.name",
 *              "link"       = "/lead/referral/organization/:organization_id"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(ou.notes, 1, 100)), CASE WHEN LENGTH(ou.notes) > 100 THEN '…' ELSE '' END)"
 *          }
 *     }
 * )
 */
class Outreach
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_outreach_list",
     *     "api_lead_outreach_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $id;

    /**
     * @var OutreachType
     * @Assert\NotNull(message = "Please select a Type", groups={
     *          "api_lead_outreach_add",
     *          "api_lead_outreach_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\OutreachType", inversedBy="outreaches", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_type", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_outreach_list",
     *     "api_lead_outreach_get"
     * })
     */
    private $type;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Organization", inversedBy="outreaches", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_organization", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_outreach_list",
     *     "api_lead_outreach_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $organization;

    /**
     * @var Contact
     * @Assert\NotNull(message = "Please select a Contact", groups={
     *          "api_lead_outreach_add",
     *          "api_lead_outreach_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Contact", inversedBy="outreaches", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_contact", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_outreach_list",
     *     "api_lead_outreach_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $contact;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *          "api_lead_outreach_add",
     *          "api_lead_outreach_edit"
     * })
     * @Assert\DateTime(groups={
     *          "api_lead_outreach_add",
     *          "api_lead_outreach_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_lead_outreach_list",
     *     "api_lead_outreach_get"
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
     *          "api_lead_outreach_add",
     *          "api_lead_outreach_edit"
     * })
     * @Groups({
     *     "api_lead_outreach_list",
     *     "api_lead_outreach_get"
     * })
     */
    private $notes;

    /**
     * @var ArrayCollection
     * @Assert\NotNull(message = "Please select at least one User.", groups={
     *     "api_lead_outreach_add",
     *     "api_lead_outreach_edit"
     * })
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="outreaches", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_lead_outreach_users",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_outreach", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_lead_outreach_list",
     *     "api_lead_outreach_get"
     * })
     */
    private $users;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Activity", mappedBy="outreach", cascade={"remove", "persist"})
     */
    private $activities;

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
     * @return OutreachType|null
     */
    public function getType(): ?OutreachType
    {
        return $this->type;
    }

    /**
     * @param OutreachType|null $type
     */
    public function setType(?OutreachType $type): void
    {
        $this->type = $type;
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
     * @return Contact|null
     */
    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    /**
     * @param Contact|null $contact
     */
    public function setContact(?Contact $contact): void
    {
        $this->contact = $contact;
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
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param $users
     */
    public function setUsers($users): void
    {
        $this->users = $users;
    }

    /**
     * @param User|null $user
     */
    public function addUser(?User $user): void
    {
        $this->users->add($user);
    }

    /**
     * @param User|null $user
     */
    public function removeUser(?User $user): void
    {
        $this->users->removeElement($user);
    }

    /**
     * @return ArrayCollection
     */
    public function getActivities(): ArrayCollection
    {
        return $this->activities;
    }

    /**
     * @param ArrayCollection $activities
     */
    public function setActivities(ArrayCollection $activities): void
    {
        $this->activities = $activities;
    }
}
