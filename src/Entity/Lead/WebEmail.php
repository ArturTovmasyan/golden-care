<?php

namespace App\Entity\Lead;

use App\Api\V1\Common\Service\PreviousAndNextItemsService;
use App\Entity\Facility;
use App\Entity\Space;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Lead\WebEmailRepository")
 * @ORM\Table(name="tbl_lead_web_email")
 * @Grid(
 *     api_lead_web_email_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "we.id"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "we.date",
 *              "link"       = "/lead/web-email/:id"
 *          },
 *          {
 *              "id"         = "facility",
 *              "type"       = "string",
 *              "field"      = "f.name"
 *          },
 *          {
 *              "id"         = "subject",
 *              "type"       = "string",
 *              "field"      = "we.subject"
 *          },
 *          {
 *              "id"         = "review_type",
 *              "type"       = "string",
 *              "field"      = "CASE WHEN ert.id IS NOT NULL THEN ert.title ELSE 'Not Reviewed' END"
 *          },
 *          {
 *              "id"         = "updated_by",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(u.firstName, ''), ' ', COALESCE(u.lastName, ''))"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "we.name"
 *          },
 *          {
 *              "id"         = "email",
 *              "type"       = "string",
 *              "field"      = "we.email"
 *          },
 *          {
 *              "id"         = "phone",
 *              "type"       = "string",
 *              "field"      = "we.phone"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class WebEmail implements PreviousAndNextItemsService
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_web_email_get",
     *     "api_lead_web_email_list"
     * })
     */
    private $id;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_lead_web_email_add",
     *     "api_lead_web_email_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_lead_web_email_add",
     *     "api_lead_web_email_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_lead_web_email_list",
     *     "api_lead_web_email_get"
     * })
     */
    private $date;

    /**
     * @var Facility
     * @ORM\ManyToOne(targetEntity="App\Entity\Facility", inversedBy="webEmails", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_lead_web_email_list",
     *     "api_lead_web_email_get"
     * })
     */
    private $facility;

    /**
     * @var EmailReviewType
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\EmailReviewType", inversedBy="webEmails", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_email_review_type", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_lead_web_email_list",
     *     "api_lead_web_email_get"
     * })
     */
    private $emailReviewType;


    /**
     * @var string
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "Email Subject cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_web_email_add",
     *          "api_lead_web_email_edit"
     *      }
     * )
     * @ORM\Column(name="subject", type="string", length=120, nullable=true)
     * @Groups({
     *     "api_lead_web_email_list",
     *     "api_lead_web_email_get"
     * })
     */
    private $subject;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=120, nullable=true)
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_web_email_add",
     *          "api_lead_web_email_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_web_email_list",
     *     "api_lead_web_email_get"
     * })
     */
    private $name;


    /**
     * @var string
     * @Assert\Email(
     *     groups={
     *          "api_lead_web_email_add",
     *          "api_lead_web_email_edit"
     *     }
     * )
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Email cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_web_email_add",
     *          "api_lead_web_email_edit"
     *      }
     * )
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Groups({
     *     "api_lead_web_email_list",
     *     "api_lead_web_email_get"
     * })
     */
    private $email;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\([0-9]{3}\)\s?[0-9]{3}-[0-9]{4}$/",
     *     message="Invalid phone number format. Valid format is (XXX) XXX-XXXX.",
     *     groups={
     *          "api_lead_web_email_add",
     *          "api_lead_web_email_edit"
     * })
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     * @Groups({
     *     "api_lead_web_email_list",
     *     "api_lead_web_email_get"
     * })
     */
    private $phone;

    /**
     * @var string $message
     * @ORM\Column(name="message", type="text", length=2048, nullable=true)
     * @Assert\Length(
     *      max = 2048,
     *      maxMessage = "Email Message cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_web_email_add",
     *          "api_lead_web_email_edit"
     * })
     * @Groups({
     *     "api_lead_web_email_list",
     *     "api_lead_web_email_get"
     * })
     */
    private $message;

    /**
     * @var bool
     * @ORM\Column(name="emailed", type="boolean")
     * @Groups({
     *     "api_lead_web_email_list",
     *     "api_lead_web_email_get"
     * })
     */
    private $emailed;

    /**
     * @var ReferrerType
     * @Assert\NotNull(message = "Please select a Type", groups={
     *          "api_lead_web_email_add",
     *          "api_lead_web_email_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\ReferrerType", inversedBy="webEmails", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_type", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_web_email_list",
     *     "api_lead_web_email_get"
     * })
     */
    private $type;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_lead_web_email_add",
     *     "api_lead_web_email_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="leadWebEmails")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_web_email_list",
     *     "api_lead_web_email_get"
     * })
     */
    private $space;

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
     * @Serializer\SerializedName("previous_email_id")
     * @Serializer\Groups({
     *     "api_lead_web_email_get"
     * })
     * @return int|null
     */
    public function getPreviousEmailId(): ?int
    {
        return $this->previousId;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("next_email_id")
     * @Serializer\Groups({
     *     "api_lead_web_email_get"
     * })
     * @return int|null
     */
    public function getNextEmailId(): ?int
    {
        return $this->nextId;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return EmailReviewType|null
     */
    public function getEmailReviewType(): ?EmailReviewType
    {
        return $this->emailReviewType;
    }

    /**
     * @param EmailReviewType|null $emailReviewType
     */
    public function setEmailReviewType(?EmailReviewType $emailReviewType): void
    {
        $this->emailReviewType = $emailReviewType;
    }

    /**
     * @return null|string
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param null|string $subject
     */
    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
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
        $this->name = $name;
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
     * @return null|string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param null|string $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return null|string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param null|string $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isEmailed(): bool
    {
        return $this->emailed;
    }

    /**
     * @param bool $emailed
     */
    public function setEmailed(bool $emailed): void
    {
        $this->emailed = $emailed;
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
