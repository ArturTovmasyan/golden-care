<?php

namespace App\Entity\Lead;

use App\Entity\CityStateZip;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_lead_organization")
 * @ORM\Entity(repositoryClass="App\Repository\Lead\OrganizationRepository")
 * @Grid(
 *     api_lead_organization_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "o.id"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "o.name",
 *              "link"       = "/lead/referral/organization/:id"
 *          },
 *          {
 *              "id"         = "category",
 *              "type"       = "string",
 *              "field"      = "c.title"
 *          },
 *          {
 *              "id"         = "address_1",
 *              "type"       = "string",
 *              "field"      = "o.address_1"
 *          },
 *          {
 *              "id"         = "address_2",
 *              "type"       = "string",
 *              "field"      = "o.address_2"
 *          },
 *          {
 *              "id"         = "csz_str",
 *              "type"       = "string",
 *              "field"      = "CONCAT(csz.city, ' ', csz.stateAbbr, ', ', csz.zipMain)"
 *          },
 *          {
 *              "id"         = "website_url",
 *              "type"       = "string",
 *              "field"      = "o.websiteUrl"
 *          },
 *          {
 *              "id"         = "referrals",
 *              "type"       = "number",
 *              "field"      = "(SELECT COUNT(r.id) FROM App\Entity\Lead\Referral r WHERE r.organization=o.id)"
 *          }
 *     }
 * )
 */
class Organization
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_organization_list",
     *     "api_lead_organization_get",
     *     "api_lead_referral_list",
     *     "api_lead_referral_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_lead_contact_list",
     *     "api_lead_contact_get",
     *     "api_lead_outreach_list",
     *     "api_lead_outreach_get",
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *     "api_lead_organization_add",
     *     "api_lead_organization_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_organization_add",
     *          "api_lead_organization_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_organization_list",
     *     "api_lead_organization_get",
     *     "api_lead_referral_list",
     *     "api_lead_referral_get",
     *     "api_lead_activity_list",
     *     "api_lead_activity_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     *     "api_lead_contact_list",
     *     "api_lead_contact_get",
     *     "api_lead_outreach_list",
     *     "api_lead_outreach_get",
     * })
     */
    private $name;

    /**
     * @var ReferrerType
     * @Assert\NotNull(message = "Please select a Category", groups={
     *          "api_lead_organization_add",
     *          "api_lead_organization_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\ReferrerType", inversedBy="organizations", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_category", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_organization_list",
     *     "api_lead_organization_get"
     * })
     */
    private $category;

    /**
     * @var string
     * @ORM\Column(name="address_1", type="string", length=100)
     * @Assert\NotBlank(groups={
     *     "api_lead_organization_edit",
     *     "api_lead_organization_add"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Address cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_organization_add",
     *          "api_lead_organization_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_organization_list",
     *     "api_lead_organization_get"
     * })
     */
    private $address_1;

    /**
     * @var string
     * @ORM\Column(name="address_2", type="string", length=100, nullable=true)
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Address (Optional) cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_organization_add",
     *          "api_lead_organization_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_organization_list",
     *     "api_lead_organization_get"
     * })
     */
    private $address_2;

    /**
     * @var CityStateZip
     * @ORM\ManyToOne(targetEntity="App\Entity\CityStateZip", inversedBy="organizations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *     message = "Please select a City, State & Zip",
     *     groups={
     *          "api_lead_organization_add",
     *          "api_organization_edit"
     *     }
     * )
     * @Groups({
     *     "api_lead_organization_list",
     *     "api_lead_organization_get"
     * })
     */
    private $csz;

    /**
     * @var string
     * @ORM\Column(name="website_url", type="string", length=100, nullable=true)
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Website URL cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_organization_add",
     *          "api_lead_organization_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_organization_list",
     *     "api_lead_organization_get"
     * })
     */
    private $websiteUrl;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\OrganizationPhone", mappedBy="organization")
     * @Assert\Valid(groups={
     *     "api_lead_organization_add",
     *     "api_lead_organization_edit"
     * })
     * @Groups({
     *      "api_lead_organization_list",
     *      "api_lead_organization_get"
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
     *          "api_lead_organization_add",
     *          "api_lead_organization_edit"
     * })
     * @Groups({
     *     "api_lead_organization_list",
     *     "api_lead_organization_get"
     * })
     */
    private $emails = [];

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Referral", mappedBy="organization", cascade={"remove", "persist"})
     */
    private $referrals;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Activity", mappedBy="organization", cascade={"remove", "persist"})
     */
    private $activities;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Contact", mappedBy="organization", cascade={"remove", "persist"})
     */
    private $contacts;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Outreach", mappedBy="organization", cascade={"remove", "persist"})
     */
    private $outreaches;

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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName(?string $name): void
    {
        $name = preg_replace('/\s\s+/', ' ', $name);
        $this->name = $name;
    }

    /**
     * @return ReferrerType|null
     */
    public function getCategory(): ?ReferrerType
    {
        return $this->category;
    }

    /**
     * @param ReferrerType|null $category
     */
    public function setCategory(?ReferrerType $category): void
    {
        $this->category = $category;
    }

    /**
     * @return null|string
     */
    public function getAddress1(): ?string
    {
        return $this->address_1;
    }

    /**
     * @param null|string $address_1
     */
    public function setAddress1(?string $address_1): void
    {
        $this->address_1 = $address_1;
    }

    /**
     * @return null|string
     */
    public function getAddress2(): ?string
    {
        return $this->address_2;
    }

    /**
     * @param null|string $address_2
     */
    public function setAddress2(?string $address_2): void
    {
        $this->address_2 = $address_2;
    }

    /**
     * @return CityStateZip|null
     */
    public function getCsz(): ?CityStateZip
    {
        return $this->csz;
    }

    /**
     * @param CityStateZip|null $csz
     */
    public function setCsz(?CityStateZip $csz): void
    {
        $this->csz = $csz;
    }

    /**
     * @return null|string
     */
    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    /**
     * @param null|string $websiteUrl
     */
    public function setWebsiteUrl(?string $websiteUrl): void
    {
        $this->websiteUrl = $websiteUrl;
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
    public function getReferrals(): ArrayCollection
    {
        return $this->referrals;
    }

    /**
     * @param ArrayCollection $referrals
     */
    public function setReferrals(ArrayCollection $referrals): void
    {
        $this->referrals = $referrals;
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
     * @return ArrayCollection
     */
    public function getContacts(): ArrayCollection
    {
        return $this->contacts;
    }

    /**
     * @param ArrayCollection $contacts
     */
    public function setContacts(ArrayCollection $contacts): void
    {
        $this->contacts = $contacts;
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

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback(groups={
     *      "api_lead_organization_add",
     *      "api_lead_organization_edit"
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
