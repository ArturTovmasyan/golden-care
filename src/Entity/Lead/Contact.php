<?php

namespace App\Entity\Lead;

use App\Entity\Space;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_lead_contact")
 * @ORM\Entity(repositoryClass="App\Repository\Lead\ContactRepository")
 * @UniqueEntity(fields="email", message="This Email address is already in use.", groups={
 *     "api_lead_contact_add",
 *     "api_lead_contact_edit"
 * })
 * @Grid(
 *     api_lead_contact_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "c.id"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(c.firstName, ''), ' ', COALESCE(c.lastName, ''))",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "organization",
 *              "type"       = "string",
 *              "field"      = "o.title",
 *              "link"       = "/lead/referral/organization/:organization_id"
 *          },
 *          {
 *              "id"         = "email",
 *              "type"       = "string",
 *              "field"      = "c.email",
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(c.notes, 1, 100)), CASE WHEN LENGTH(c.notes) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "created_by",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(u.firstName, ''), ' ', COALESCE(u.lastName, ''))"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class Contact
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_contact_list",
     *     "api_lead_contact_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *          "api_lead_contact_add",
     *          "api_lead_contact_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "First Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_contact_add",
     *          "api_lead_contact_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_contact_list",
     *     "api_lead_contact_get"
     * })
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *          "api_lead_contact_add",
     *          "api_lead_contact_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "Last Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_contact_add",
     *          "api_lead_contact_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_contact_list",
     *     "api_lead_contact_get"
     * })
     */
    private $lastName;

    /**
     * @var Organization
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Organization", inversedBy="contacts", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_organization", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_contact_list",
     *     "api_lead_contact_get"
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
     *          "api_lead_contact_add",
     *          "api_lead_contact_edit"
     * })
     * @Groups({
     *     "api_lead_contact_list",
     *     "api_lead_contact_get"
     * })
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\ContactPhone", mappedBy="contact")
     * @Assert\Valid(groups={
     *          "api_lead_contact_add",
     *          "api_lead_contact_edit"
     * })
     * @Groups({
     *      "api_lead_contact_list",
     *      "api_lead_contact_get"
     * })
     */
    private $phones;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={
     *     "api_lead_contact_add",
     *     "api_lead_contact_edit"
     * })
     * @Assert\Email(groups={
     *     "api_lead_contact_add",
     *     "api_lead_contact_edit"
     * })
     * @Groups({
     *      "api_lead_contact_list",
     *      "api_lead_contact_get"
     * })
     */
    private $email;

    /**
     * @var array $emails
     * @ORM\Column(name="emails", type="json_array", nullable=true)
     * @Assert\Count(
     *      max = 10,
     *      maxMessage = "You cannot enter more than {{ limit }} email addresses",
     *      groups={
     *          "api_lead_contact_add",
     *          "api_lead_contact_edit"
     * })
     * @Groups({
     *     "api_lead_contact_list",
     *     "api_lead_contact_get"
     * })
     */
    private $emails = [];

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_lead_contact_add",
     *     "api_lead_contact_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="leadContacts")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_contact_list",
     *     "api_lead_contact_get"
     * })
     */
    private $space;

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
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
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
     * @param ExecutionContextInterface $context
     * @Assert\Callback(groups={
     *          "api_lead_contact_add",
     *          "api_lead_contact_edit"
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
