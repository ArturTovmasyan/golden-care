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
 *              "field"      = "we.date"
 *          },
 *          {
 *              "id"         = "facility",
 *              "type"       = "string",
 *              "field"      = "f.name"
 *          },
 *          {
 *              "id"         = "review_type",
 *              "type"       = "string",
 *              "field"      = "CASE WHEN ert.id IS NOT NULL THEN ert.title ELSE 'Not Reviewed' END",
 *              "link"       = "/lead/web-email/:id"
 *          },
 *          {
 *              "id"         = "updated_by",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(u.firstName, ''), ' ', COALESCE(u.lastName, ''))"
 *          },
 *          {
 *              "id"         = "subject",
 *              "type"       = "string",
 *              "field"      = "we.subject"
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
     * @var string $body
     * @ORM\Column(name="body", type="text", length=2048, nullable=true)
     * @Assert\Length(
     *      max = 2048,
     *      maxMessage = "Email Body cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_web_email_add",
     *          "api_lead_web_email_edit"
     * })
     * @Groups({
     *     "api_lead_web_email_list",
     *     "api_lead_web_email_get"
     * })
     */
    private $body;

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
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param null|string $body
     */
    public function setBody(?string $body): void
    {
        $this->body = $body;
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
