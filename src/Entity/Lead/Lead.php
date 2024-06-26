<?php

namespace App\Entity\Lead;

use App\Api\V1\Common\Service\PreviousAndNextItemsService;
use App\Entity\CareLevel;
use App\Entity\CityStateZip;
use App\Entity\Facility;
use App\Entity\PaymentSource;
use App\Entity\User;
use App\Model\Lead\Qualified;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

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
 *              "id"         = "referral_id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "r.id"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "l.createdAt"
 *          },
 *          {
 *              "id"         = "responsible_person",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(l.responsiblePersonFirstName, ''), ' ', COALESCE(l.responsiblePersonLastName, ''))",
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
 *              "id"         = "funnel_stage",
 *              "type"       = "string",
 *              "field"      = "(SELECT DISTINCT fs.title FROM App:Lead\LeadFunnelStage lfs JOIN lfs.stage fs JOIN lfs.lead fsl WHERE fsl.id=l.id AND lfs.date = (SELECT MAX(lfsMax.date) FROM App:Lead\LeadFunnelStage lfsMax JOIN lfsMax.lead fslMax WHERE fslMax.id=l.id) GROUP BY fsl.id)"
 *          },
 *          {
 *              "id"         = "funnel_date",
 *              "type"       = "date",
 *              "field"      = "(SELECT DISTINCT DATE_FORMAT(lf.date, CONCAT('%Y-%m-%d', 'T', '%H:%i:%s', '.000Z'))  FROM App:Lead\LeadFunnelStage lf JOIN lf.lead lfl WHERE lfl.id=l.id AND lf.date = (SELECT MAX(lfMax.date) FROM App:Lead\LeadFunnelStage lfMax JOIN lfMax.lead lflMax WHERE lflMax.id=l.id) GROUP BY lfl.id)"
 *          },
 *          {
 *              "id"         = "temperature",
 *              "type"       = "string",
 *              "field"      = "(SELECT DISTINCT t.title FROM App:Lead\LeadTemperature lt JOIN lt.temperature t JOIN lt.lead ltl WHERE ltl.id=l.id AND lt.date = (SELECT MAX(ltMax.date) FROM App:Lead\LeadTemperature ltMax JOIN ltMax.lead ltlMax WHERE ltlMax.id=l.id) GROUP BY ltl.id)"
 *          },
 *          {
 *              "id"         = "referral",
 *              "type"       = "string",
 *              "field"      = "COALESCE(CASE WHEN r.id IS NOT NULL AND rc.id IS NOT NULL THEN CONCAT(COALESCE(rc.firstName, ''), ' ', COALESCE(rc.lastName, '')) WHEN r.id IS NOT NULL AND rc.id IS NULL AND ro.id IS NOT NULL THEN ro.name ELSE rt.title END, '<No Referral>')",
 *              "link"       = "/lead/referral/:referral_id"
 *          },
 *          {
 *              "id"         = "primary_facility",
 *              "type"       = "string",
 *              "field"      = "CASE WHEN l.primaryFacility IS NOT NULL THEN CONCAT(f.name, ' (', f.shorthand, ')') ELSE '-' END"
 *          }
 *     }
 * )
 */
class Lead implements PreviousAndNextItemsService
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
     *     "api_lead_activity_get",
     *     "api_lead_lead_funnel_stage_list",
     *     "api_lead_lead_funnel_stage_get",
     *     "api_lead_lead_temperature_list",
     *     "api_lead_lead_temperature_get"
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
     *      maxMessage = "First Name cannot be longer than {{ limit }} characters",
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
     *     "api_lead_activity_get",
     *     "api_lead_lead_funnel_stage_list",
     *     "api_lead_lead_funnel_stage_get",
     *     "api_lead_lead_temperature_list",
     *     "api_lead_lead_temperature_get"
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
     *      maxMessage = "Last Name cannot be longer than {{ limit }} characters",
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
     *     "api_lead_activity_get",
     *     "api_lead_lead_funnel_stage_list",
     *     "api_lead_lead_funnel_stage_get",
     *     "api_lead_lead_temperature_list",
     *     "api_lead_lead_temperature_get"
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
     * @var CareLevel
     * @ORM\ManyToOne(targetEntity="App\Entity\CareLevel", inversedBy="leads", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_care_level", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $careLevel;

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
     * @var \DateTime
     * @ORM\Column(name="birthday", type="date", nullable=true)
     * @Assert\Date(groups={
     *     "api_lead_lead_add",
     *     "api_lead_lead_edit"
     * })
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $birthday;

    /**
     * @var string
     * @ORM\Column(name="spouse_name", type="string", length=120, nullable=true)
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "Spouse's Name cannot be longer than {{ limit }} characters",
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
     *     "api_lead_activity_get",
     *     "api_lead_lead_funnel_stage_list",
     *     "api_lead_lead_funnel_stage_get",
     *     "api_lead_lead_temperature_list",
     *     "api_lead_lead_temperature_get"
     * })
     */
    private $spouseName;

    /**
     * @var CurrentResidence
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\CurrentResidence", inversedBy="leads", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_current_residence", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $currentResidence;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Lead\Hobby", inversedBy="leads", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_lead_hobbies",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_lead", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_hobby", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $hobbies;

    /**
     * @var string
     * @ORM\Column(name="rp_first_name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *     "api_lead_lead_add",
     *     "api_lead_lead_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "RP First Name cannot be longer than {{ limit }} characters",
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
     *      maxMessage = "RP Last Name cannot be longer than {{ limit }} characters",
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
     *      maxMessage = "RP Address cannot be longer than {{ limit }} characters",
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
     *      maxMessage = "RP Address (optional) cannot be longer than {{ limit }} characters",
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
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Email cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit"
     *      }
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
     * @ORM\Column(name="notes", type="text", length=2048, nullable=true)
     * @Assert\Length(
     *      max = 2048,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit",
     *          "api_lead_lead_interest_edit"
     * })
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $notes;

    /**
     * @var bool
     * @ORM\Column(name="web_lead", type="boolean")
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $webLead = false;

    /**
     * @var bool
     * @ORM\Column(name="spam", type="boolean")
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $spam = false;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *      "api_lead_lead_add",
     *      "api_lead_lead_edit"
     * })
     * @ORM\Column(name="spam_updated", type="datetime", nullable=true)
     */
    private $spamUpdated;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Activity", mappedBy="lead", cascade={"remove", "persist"})
     */
    private $activities;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\LeadFunnelStage", mappedBy="lead", cascade={"remove", "persist"})
     */
    private $leadFunnelStages;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\LeadTemperature", mappedBy="lead", cascade={"remove", "persist"})
     */
    private $leadTemperatures;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Assessment", mappedBy="lead")
     */
    private $assessments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\LeadQualificationRequirement", mappedBy="lead", cascade={"persist"})
     * @Assert\Valid(groups={
     *      "api_lead_lead_add",
     *      "api_lead_lead_edit",
     *      "api_lead_lead_qualification_edit"
     * })
     * @Groups({
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     * @Serializer\SerializedName("qualifications")
     */
    private $leadQualificationRequirements;

    /**
     * @var int
     * @ORM\Column(name="qualified", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\Lead\Qualified","getTypeValues"},
     *     groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit",
     *          "api_lead_lead_qualification_edit"
     *     }
     * )
     * @Groups({
     *      "api_lead_lead_list",
     *      "api_lead_lead_get"
     * })
     */
    private $qualified;

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
     * @var int
     */
    private $previousId;

    /**
     * @var int
     */
    private $nextId;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("previous_lead_id")
     * @Serializer\Groups({
     *     "api_lead_lead_get"
     * })
     * @return int|null
     */
    public function getPreviousLeadId(): ?int
    {
        return $this->previousId;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("next_lead_id")
     * @Serializer\Groups({
     *     "api_lead_lead_get"
     * })
     * @return int|null
     */
    public function getNextLeadId(): ?int
    {
        return $this->nextId;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("funnel_stage")
     * @Serializer\Groups({
     *     "api_lead_lead_get"
     * })
     * @return string
     */
    public function getFunnelStage(): string
    {
        $funnelStage = '';

        $criteria = Criteria::create()
            ->orderBy(array('date' => Criteria::DESC))
            ->setMaxResults(1)
        ;

        /** @var LeadFunnelStage[] $data */
        $data = $this->leadFunnelStages->matching($criteria);

        if(\count($data) > 0) {
            $funnelStage = $data[0]->getStage() ? $data[0]->getStage()->getTitle() : '';
        }

        return $funnelStage;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("temperature")
     * @Serializer\Groups({
     *     "api_lead_lead_get"
     * })
     * @return string
     */
    public function getTemperature(): string
    {
        $temperature = '';

        $criteria = Criteria::create()
            ->orderBy(array('date' => Criteria::DESC))
            ->setMaxResults(1)
        ;

        /** @var LeadTemperature[] $data */
        $data = $this->leadTemperatures->matching($criteria);

        if(\count($data) > 0) {
            $temperature = $data[0]->getTemperature() ? $data[0]->getTemperature()->getTitle() : '';
        }

        return $temperature;
    }

    /**
     * @return int
     */
    public function getId(): ?int
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
     * @return CareLevel|null
     */
    public function getCareLevel(): ?CareLevel
    {
        return $this->careLevel;
    }

    /**
     * @param CareLevel|null $careLevel
     */
    public function setCareLevel(?CareLevel $careLevel): void
    {
        $this->careLevel = $careLevel;
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
     * @return \DateTime|null
     */
    public function getBirthday(): ?\DateTime
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime|null $birthday
     */
    public function setBirthday(?\DateTime $birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @return null|string
     */
    public function getSpouseName(): ?string
    {
        return $this->spouseName;
    }

    /**
     * @param null|string $spouseName
     */
    public function setSpouseName(?string $spouseName): void
    {
        $this->spouseName = $spouseName;
    }

    /**
     * @return CurrentResidence|null
     */
    public function getCurrentResidence(): ?CurrentResidence
    {
        return $this->currentResidence;
    }

    /**
     * @param CurrentResidence|null $currentResidence
     */
    public function setCurrentResidence(?CurrentResidence $currentResidence): void
    {
        $this->currentResidence = $currentResidence;
    }

    /**
     * @return mixed
     */
    public function getHobbies()
    {
        return $this->hobbies;
    }

    /**
     * @param $hobbies
     */
    public function setHobbies($hobbies): void
    {
        $this->hobbies = $hobbies;
    }

    /**
     * @param Hobby|null $hobby
     */
    public function addHobby(?Hobby $hobby): void
    {
        $this->hobbies->add($hobby);
    }

    /**
     * @param Hobby|null $hobby
     */
    public function removeHobby(?Hobby $hobby): void
    {
        $this->hobbies->removeElement($hobby);
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
     * @return bool
     */
    public function isWebLead(): bool
    {
        return $this->webLead;
    }

    /**
     * @param bool $webLead
     */
    public function setWebLead(bool $webLead): void
    {
        $this->webLead = $webLead;
    }

    /**
     * @return bool
     */
    public function isSpam(): bool
    {
        return $this->spam;
    }

    /**
     * @param bool $spam
     */
    public function setSpam(bool $spam): void
    {
        $this->spam = $spam;
    }

    /**
     * @return \DateTime|null
     */
    public function getSpamUpdated(): ?\DateTime
    {
        return $this->spamUpdated;
    }

    /**
     * @param \DateTime|null $spamUpdated
     */
    public function setSpamUpdated(?\DateTime $spamUpdated): void
    {
        $this->spamUpdated = $spamUpdated;
    }

    /**
     * @return Collection
     */
    public function getActivities(): Collection
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

    /**
     * @return ArrayCollection
     */
    public function getLeadTemperatures(): ArrayCollection
    {
        return $this->leadTemperatures;
    }

    /**
     * @param ArrayCollection $leadTemperatures
     */
    public function setLeadTemperatures(ArrayCollection $leadTemperatures): void
    {
        $this->leadTemperatures = $leadTemperatures;
    }

    /**
     * @return mixed
     */
    public function getAssessments()
    {
        return $this->assessments;
    }

    /**
     * @param mixed $assessments
     */
    public function setAssessments($assessments): void
    {
        $this->assessments = $assessments;
    }

    /**
     * @return mixed
     */
    public function getLeadQualificationRequirements()
    {
        return $this->leadQualificationRequirements;
    }

    /**
     * @param mixed $leadQualificationRequirements
     */
    public function setLeadQualificationRequirements($leadQualificationRequirements): void
    {
        $this->leadQualificationRequirements = $leadQualificationRequirements;
    }

    /**
     * @return int|null
     */
    public function getQualified(): ?int
    {
        return $this->qualified;
    }

    /**
     * @param int|null $qualified
     */
    public function setQualified(?int $qualified): void
    {
        $this->qualified = $qualified;
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

    /**
     * @return int|null
     */
    public function getPreviousId(): ?int
    {
        return $this->previousId;
    }

    /**
     * @param int|null $previousId
     */
    public function setPreviousId(?int $previousId): void
    {
        $this->previousId = $previousId;
    }

    /**
     * @return int|null
     */
    public function getNextId(): ?int
    {
        return $this->nextId;
    }

    /**
     * @param int|null $nextId
     */
    public function setNextId(?int $nextId): void
    {
        $this->nextId = $nextId;
    }
}
