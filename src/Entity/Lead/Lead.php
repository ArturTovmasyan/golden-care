<?php

namespace App\Entity\Lead;

use App\Entity\CityStateZip;
use App\Entity\Facility;
use App\Entity\PaymentSource;
use App\Entity\User;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_lead_lead")
 * @ORM\Entity(repositoryClass="App\Repository\Lead\LeadRepository")
 * @Grid(
 *     api_lead_lead_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "l.id"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "l.createdAt"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(l.firstName, ''), ' ', COALESCE(l.lastName, ''))",
 *              "link"       = "/lead/lead/:id"
 *          },
 *          {
 *              "id"         = "owner",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(o.firstName, ''), ' ', COALESCE(o.lastName, ''))"
 *          },
 *          {
 *              "id"         = "state",
 *              "type"       = "enum",
 *              "field"      = "l.state",
 *              "values"     = "\App\Model\Lead\State::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "state_change_reason",
 *              "type"       = "string",
 *              "field"      = "scr.title"
 *          },
 *          {
 *              "id"         = "effective_date",
 *              "type"       = "datetime",
 *              "field"      = "l.stateEffectiveDate"
 *          },
 *          {
 *              "id"         = "referral",
 *              "type"       = "string",
 *              "field"      = "CASE WHEN r.firstName IS NOT NULL THEN CONCAT(COALESCE(r.firstName, ''), ' ', COALESCE(r.lastName, '')) ELSE ro.title END"
 *          }
 *     }
 * )
 */
class Lead
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_lead_referral_list",
     *     "api_lead_referral_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *     "api_lead_lead_add",
     *     "api_lead_lead_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "FirstName cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_lead_referral_list",
     *     "api_lead_referral_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *     "api_lead_lead_add",
     *     "api_lead_lead_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "LastName cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_lead_referral_list",
     *     "api_lead_referral_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $lastName;

    /**
     * @var CareType
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\CareType", inversedBy="leads", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_care_type", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $careType;

    /**
     * @var PaymentSource
     * @ORM\ManyToOne(targetEntity="App\Entity\PaymentSource", inversedBy="leads", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_payment_type", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $paymentType;

    /**
     * @var User
     * @Assert\NotNull(message = "Please select an Owner", groups={
     *     "api_lead_lead_add",
     *     "api_lead_lead_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="leads", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_owner", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $owner;

    /**
     * @var int
     * @ORM\Column(name="state", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\Lead\State","getTypeValues"},
     *     groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit"
     *     }
     * )
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $state;

    /**
     * @var StateChangeReason
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\StateChangeReason", inversedBy="leads", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_state_change_reason", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $stateChangeReason;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *     "api_lead_lead_add",
     *     "api_lead_lead_edit"
     * })
     * @ORM\Column(name="state_effective_date", type="datetime", nullable=true)
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $stateEffectiveDate;

    /**
     * @var string
     * @ORM\Column(name="rp_first_name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *     "api_lead_lead_add",
     *     "api_lead_lead_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "RP firstName cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $responsiblePersonFirstName;

    /**
     * @var string
     * @ORM\Column(name="rp_last_name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *     "api_lead_lead_add",
     *     "api_lead_lead_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "RP lastName cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get"
     * })
     */
    private $responsiblePersonLastName;

    /**
     * @var string
     * @ORM\Column(name="rp_address_1", type="string", length=100, nullable=true)
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "RP address cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $responsiblePersonAddress_1;

    /**
     * @var string
     * @ORM\Column(name="rp_address_2", type="string", length=100, nullable=true)
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "RP address (optional) cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $responsiblePersonAddress_2;

    /**
     * @var CityStateZip
     * @ORM\ManyToOne(targetEntity="App\Entity\CityStateZip", inversedBy="leads")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rp_csz_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $responsiblePersonCsz;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\([0-9]{3}\)\s?[0-9]{3}-[0-9]{4}$/",
     *     message="Invalid phone number format. Valid format is (XXX) XXX-XXXX.",
     *     groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit"
     * })
     * @ORM\Column(name="rp_phone", type="string", length=20, nullable=true)
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $responsiblePersonPhone;

    /**
     * @var string
     * @Assert\Email(
     *     groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit"
     *     }
     * )
     * @ORM\Column(name="rp_email", type="string", length=255, nullable=true)
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $responsiblePersonEmail;

    /**
     * @var Referral
     * @ORM\OneToOne(targetEntity="App\Entity\Lead\Referral", mappedBy="lead", cascade={"remove", "persist"})
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $referral;

    /**
     * @var Facility
     * @ORM\ManyToOne(targetEntity="App\Entity\Facility", inversedBy="leads")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $primaryFacility;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Facility", inversedBy="facilityLeads", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_lead_facilities",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_lead", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $facilities;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit"
     * })
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $notes;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Activity", mappedBy="lead", cascade={"remove", "persist"})
     */
    private $activities;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_lead_lead_add"
     * })
     * @Assert\DateTime(groups={
     *     "api_lead_lead_add"
     * })
     */
    private $initialContactDate;

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
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param null|string $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return null|string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param null|string $lastName
     */
    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return CareType|null
     */
    public function getCareType(): ?CareType
    {
        return $this->careType;
    }

    /**
     * @param CareType|null $careType
     */
    public function setCareType(?CareType $careType): void
    {
        $this->careType = $careType;
    }

    /**
     * @return PaymentSource|null
     */
    public function getPaymentType(): ?PaymentSource
    {
        return $this->paymentType;
    }

    /**
     * @param PaymentSource|null $paymentType
     */
    public function setPaymentType(?PaymentSource $paymentType): void
    {
        $this->paymentType = $paymentType;
    }

    /**
     * @return User|null
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User|null $owner
     */
    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return int|null
     */
    public function getState(): ?int
    {
        return $this->state;
    }

    /**
     * @param int|null $state
     */
    public function setState(?int $state): void
    {
        $this->state = $state;
    }

    /**
     * @return StateChangeReason|null
     */
    public function getStateChangeReason(): ?StateChangeReason
    {
        return $this->stateChangeReason;
    }

    /**
     * @param StateChangeReason|null $stateChangeReason
     */
    public function setStateChangeReason(?StateChangeReason $stateChangeReason): void
    {
        $this->stateChangeReason = $stateChangeReason;
    }

    /**
     * @return \DateTime|null
     */
    public function getStateEffectiveDate(): ?\DateTime
    {
        return $this->stateEffectiveDate;
    }

    /**
     * @param \DateTime|null $stateEffectiveDate
     */
    public function setStateEffectiveDate(?\DateTime $stateEffectiveDate): void
    {
        $this->stateEffectiveDate = $stateEffectiveDate;
    }

    /**
     * @return null|string
     */
    public function getResponsiblePersonFirstName(): ?string
    {
        return $this->responsiblePersonFirstName;
    }

    /**
     * @param null|string $responsiblePersonFirstName
     */
    public function setResponsiblePersonFirstName(?string $responsiblePersonFirstName): void
    {
        $this->responsiblePersonFirstName = $responsiblePersonFirstName;
    }

    /**
     * @return null|string
     */
    public function getResponsiblePersonLastName(): ?string
    {
        return $this->responsiblePersonLastName;
    }

    /**
     * @param null|string $responsiblePersonLastName
     */
    public function setResponsiblePersonLastName(?string $responsiblePersonLastName): void
    {
        $this->responsiblePersonLastName = $responsiblePersonLastName;
    }

    /**
     * @return null|string
     */
    public function getResponsiblePersonAddress1(): ?string
    {
        return $this->responsiblePersonAddress_1;
    }

    /**
     * @param null|string $responsiblePersonAddress_1
     */
    public function setResponsiblePersonAddress1(?string $responsiblePersonAddress_1): void
    {
        $this->responsiblePersonAddress_1 = $responsiblePersonAddress_1;
    }

    /**
     * @return null|string
     */
    public function getResponsiblePersonAddress2(): ?string
    {
        return $this->responsiblePersonAddress_2;
    }

    /**
     * @param null|string $responsiblePersonAddress_2
     */
    public function setResponsiblePersonAddress2(?string $responsiblePersonAddress_2): void
    {
        $this->responsiblePersonAddress_2 = $responsiblePersonAddress_2;
    }

    /**
     * @return CityStateZip|null
     */
    public function getResponsiblePersonCsz(): ?CityStateZip
    {
        return $this->responsiblePersonCsz;
    }

    /**
     * @param CityStateZip|null $responsiblePersonCsz
     */
    public function setResponsiblePersonCsz(?CityStateZip $responsiblePersonCsz): void
    {
        $this->responsiblePersonCsz = $responsiblePersonCsz;
    }

    /**
     * @return null|string
     */
    public function getResponsiblePersonPhone(): ?string
    {
        return $this->responsiblePersonPhone;
    }

    /**
     * @param null|string $responsiblePersonPhone
     */
    public function setResponsiblePersonPhone(?string $responsiblePersonPhone): void
    {
        $this->responsiblePersonPhone = $responsiblePersonPhone;
    }

    /**
     * @return null|string
     */
    public function getResponsiblePersonEmail(): ?string
    {
        return $this->responsiblePersonEmail;
    }

    /**
     * @param null|string $responsiblePersonEmail
     */
    public function setResponsiblePersonEmail(?string $responsiblePersonEmail): void
    {
        $this->responsiblePersonEmail = $responsiblePersonEmail;
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
     * @return Facility|null
     */
    public function getPrimaryFacility(): ?Facility
    {
        return $this->primaryFacility;
    }

    /**
     * @param Facility|null $primaryFacility
     */
    public function setPrimaryFacility(?Facility $primaryFacility): void
    {
        $this->primaryFacility = $primaryFacility;
    }

    /**
     * @return mixed
     */
    public function getFacilities()
    {
        return $this->facilities;
    }

    /**
     * @param $facilities
     */
    public function setFacilities($facilities): void
    {
        $this->facilities = $facilities;
    }

    /**
     * @param Facility|null $facility
     */
    public function addFacility(?Facility $facility): void
    {
        $this->facilities->add($facility);
    }

    /**
     * @param Facility|null $facility
     */
    public function removeFacility(?Facility $facility): void
    {
        $this->facilities->removeElement($facility);
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

    /**
     * @return \DateTime|null
     */
    public function getInitialContactDate(): ?\DateTime
    {
        return $this->initialContactDate;
    }

    /**
     * @param \DateTime|null $initialContactDate
     */
    public function setInitialContactDate(?\DateTime $initialContactDate): void
    {
        $this->initialContactDate = $initialContactDate;
    }
}
