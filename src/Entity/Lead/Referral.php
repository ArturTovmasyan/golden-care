<?php

namespace App\Entity\Lead;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_lead_referral")
 * @ORM\Entity(repositoryClass="App\Repository\Lead\ReferralRepository")
 * @Grid(
 *     api_lead_referral_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "r.id"
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
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(r.firstName, ''), ' ', COALESCE(r.lastName, ''))",
 *              "link"       = "/lead/referral/:id"
 *          },
 *          {
 *              "id"         = "type",
 *              "type"       = "string",
 *              "field"      = "rt.title"
 *          },
 *          {
 *              "id"         = "lead",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(l.firstName, ''), ' ', COALESCE(l.lastName, ''))",
 *              "link"       = "/lead/lead/:lead_id"
 *          },
 *          {
 *              "id"         = "organization",
 *              "type"       = "string",
 *              "field"      = "o.title",
 *              "link"       = "/lead/referral/organization/:organization_id"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(r.notes, 1, 100)), CASE WHEN LENGTH(r.notes) > 100 THEN '…' ELSE '' END)"
 *          }
 *     }
 * )
 */
class Referral
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_referral_list",
     *     "api_lead_referral_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=60, nullable=true)
     * @Assert\NotBlank(groups={
     *          "api_lead_referral_representative_required_add",
     *          "api_lead_referral_representative_required_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "First Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_referral_representative_required_add",
     *          "api_lead_referral_representative_required_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_referral_list",
     *     "api_lead_referral_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=60, nullable=true)
     * @Assert\NotBlank(groups={
     *          "api_lead_referral_representative_required_add",
     *          "api_lead_referral_representative_required_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "Last Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_referral_representative_required_add",
     *          "api_lead_referral_representative_required_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_referral_list",
     *     "api_lead_referral_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $lastName;

    /**
     * @var Lead
     * @Assert\NotNull(message = "Please select a Lead", groups={
     *          "api_lead_referral_organization_required_add",
     *          "api_lead_referral_organization_required_edit",
     *          "api_lead_referral_representative_required_add",
     *          "api_lead_referral_representative_required_edit"
     * })
     * @ORM\OneToOne(targetEntity="App\Entity\Lead\Lead", inversedBy="referral", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_lead", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_referral_list",
     *     "api_lead_referral_get"
     * })
     */
    private $lead;

    /**
     * @var ReferrerType
     * @Assert\NotNull(message = "Please select a Type", groups={
     *          "api_lead_referral_organization_required_add",
     *          "api_lead_referral_organization_required_edit",
     *          "api_lead_referral_representative_required_add",
     *          "api_lead_referral_representative_required_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\ReferrerType", inversedBy="referrals", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_type", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_referral_list",
     *     "api_lead_referral_get",
     *     "api_lead_lead_get"
     * })
     */
    private $type;

    /**
     * @var Organization
     * @Assert\NotNull(message = "Please select an Organization", groups={
     *          "api_lead_referral_organization_required_add",
     *          "api_lead_referral_organization_required_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Organization", inversedBy="referrals", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_organization", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_referral_list",
     *     "api_lead_referral_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $organization;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_referral_representative_required_add",
     *          "api_lead_referral_representative_required_edit"
     * })
     * @Groups({
     *     "api_lead_referral_list",
     *     "api_lead_referral_get",
     *     "api_lead_lead_get"
     * })
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\ReferralPhone", mappedBy="referral")
     * @Assert\Valid(groups={
     *          "api_lead_referral_representative_required_add",
     *          "api_lead_referral_representative_required_edit"
     * })
     * @Groups({
     *      "api_lead_referral_list",
     *      "api_lead_referral_get",
     *      "api_lead_lead_get"
     * })
     */
    private $phones;

    /**
     * @var array $emails
     * @ORM\Column(name="emails", type="json_array", nullable=true)
     * @Assert\Count(
     *      max = 10,
     *      maxMessage = "You cannot enter more than {{ limit }} email addresses",
     *      groups={
     *          "api_lead_referral_representative_required_add",
     *          "api_lead_referral_representative_required_edit"
     * })
     * @Groups({
     *     "api_lead_referral_list",
     *     "api_lead_referral_get",
     *     "api_lead_lead_get"
     * })
     */
    private $emails = [];

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Activity", mappedBy="referral", cascade={"remove", "persist"})
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
     * @return ReferrerType|null
     */
    public function getType(): ?ReferrerType
    {
        return $this->type;
    }

    /**
     * @param ReferrerType|null $type
     */
    public function setType(?ReferrerType $type): void
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
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param mixed $phones
     */
    public function setPhones($phones): void
    {
        $this->phones = $phones;
    }

    /**
     * @return array
     */
    public function getEmails(): array
    {
        return $this->emails;
    }

    /**
     * @param array $emails
     */
    public function setEmails(array $emails): void
    {
        $this->emails = $emails;
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
     * @param ExecutionContextInterface $context
     * @Assert\Callback(groups={
     *          "api_lead_referral_representative_required_add",
     *          "api_lead_referral_representative_required_edit"
     * })
     */
    public function areEmailsValid(ExecutionContextInterface $context): void
    {
        $emails = $this->getEmails();
        $checks = [];
        foreach ($emails as $email) {
            $check = preg_match('/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/', $email);
            $checks[] = $check;
        }

        if (\count($emails) === 1 && empty($emails[0])) {
            $countEmails = 0;
        } else {
            $countEmails = \count($emails);
        }
        $checks = array_sum($checks);
        $valid = $countEmails - $checks;

        if ($valid === 1) {
            $context->buildViolation('Invalid email.')
                ->atPath('emails')
                ->addViolation();
        } elseif ($valid > 1) {
            $context->buildViolation($valid . ' invalid emails.')
                ->atPath('emails')
                ->addViolation();
        }
    }
}
