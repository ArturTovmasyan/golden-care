<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class NotificationType
 *
 * @ORM\Entity(repositoryClass="App\Repository\NotificationTypeRepository")
 * @ORM\Table(name="tbl_notification_type")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space.",
 *     groups={
 *          "api_admin_notification_type_add",
 *          "api_admin_notification_type_edit"
 *     }
 * )
 * @Grid(
 *     api_admin_notification_type_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "nt.id"
 *          },
 *          {
 *              "id"         = "category",
 *              "type"       = "enum",
 *              "field"      = "nt.category",
 *              "values"     = "\App\Model\NotificationTypeCategoryType::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "nt.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "email",
 *              "type"       = "boolean",
 *              "field"      = "nt.email"
 *          },
 *          {
 *              "id"         = "sms",
 *              "type"       = "boolean",
 *              "field"      = "nt.sms"
 *          },
 *          {
 *              "id"         = "facility",
 *              "type"       = "boolean",
 *              "field"      = "nt.facility"
 *          },
 *          {
 *              "id"         = "apartment",
 *              "type"       = "boolean",
 *              "field"      = "nt.apartment"
 *          },
 *          {
 *              "id"         = "region",
 *              "type"       = "boolean",
 *              "field"      = "nt.region"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class NotificationType
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get",
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="category", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\NotificationTypeCategoryType","getTypeValues"},
     *     groups={
     *          "api_admin_notification_type_add",
     *          "api_admin_notification_type_edit"
     *     }
     * )
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get"
     * })
     */
    private $category;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_admin_notification_type_add",
     *          "api_admin_notification_type_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_notification_type_add",
     *          "api_admin_notification_type_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=60)
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get",
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $title;

    /**
     * @var bool
     * @ORM\Column(name="email", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get"
     * })
     */
    private $email;

    /**
     * @var bool
     * @ORM\Column(name="sms", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get"
     * })
     */
    private $sms;

    /**
     * @var bool
     * @ORM\Column(name="facility", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get"
     * })
     */
    private $facility;

    /**
     * @var bool
     * @ORM\Column(name="apartment", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get"
     * })
     */
    private $apartment;

    /**
     * @var bool
     * @ORM\Column(name="region", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get"
     * })
     */
    private $region;

    /**
     * @var string
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "Email subject cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_notification_type_add",
     *          "api_admin_notification_type_edit"
     *      }
     * )
     * @ORM\Column(name="email_subject", type="string", length=120, nullable=true)
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get"
     * })
     */
    private $emailSubject;

    /**
     * @var string
     * @Assert\Length(
     *      max = 1000,
     *      maxMessage = "Email message cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_notification_type_add",
     *          "api_admin_notification_type_edit"
     *      }
     * )
     * @ORM\Column(name="email_message", type="string", length=1000, nullable=true)
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get"
     * })
     */
    private $emailMessage;

    /**
     * @var string
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "SMS subject cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_notification_type_add",
     *          "api_admin_notification_type_edit"
     *      }
     * )
     * @ORM\Column(name="sms_subject", type="string", length=120, nullable=true)
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get"
     * })
     */
    private $smsSubject;

    /**
     * @var string
     * @Assert\Length(
     *      max = 1000,
     *      maxMessage = "SMS message cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_notification_type_add",
     *          "api_admin_notification_type_edit"
     *      }
     * )
     * @ORM\Column(name="sms_message", type="string", length=1000, nullable=true)
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get"
     * })
     */
    private $smsMessage;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_notification_type_add",
     *     "api_admin_notification_type_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="notificationTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_notification_type_list",
     *     "api_admin_notification_type_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Notification", mappedBy="type", cascade={"remove", "persist"})
     */
    private $notifications;

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getCategory(): ?int
    {
        return $this->category;
    }

    /**
     * @param int|null $category
     */
    public function setCategory(?int $category): void
    {
        $this->category = $category;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $title = preg_replace('/\s\s+/', ' ', $title);
        $this->title = $title;
    }

    /**
     * @return bool
     */
    public function isEmail(): bool
    {
        return $this->email;
    }

    /**
     * @param bool $email
     */
    public function setEmail(bool $email): void
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isSms(): bool
    {
        return $this->sms;
    }

    /**
     * @param bool $sms
     */
    public function setSms(bool $sms): void
    {
        $this->sms = $sms;
    }

    /**
     * @return bool
     */
    public function isFacility(): bool
    {
        return $this->facility;
    }

    /**
     * @param bool $facility
     */
    public function setFacility(bool $facility): void
    {
        $this->facility = $facility;
    }

    /**
     * @return bool
     */
    public function isApartment(): bool
    {
        return $this->apartment;
    }

    /**
     * @param bool $apartment
     */
    public function setApartment(bool $apartment): void
    {
        $this->apartment = $apartment;
    }

    /**
     * @return bool
     */
    public function isRegion(): bool
    {
        return $this->region;
    }

    /**
     * @param bool $region
     */
    public function setRegion(bool $region): void
    {
        $this->region = $region;
    }

    /**
     * @return null|string
     */
    public function getEmailSubject(): ?string
    {
        return $this->emailSubject;
    }

    /**
     * @param null|string $emailSubject
     */
    public function setEmailSubject(?string $emailSubject): void
    {
        $this->emailSubject = $emailSubject;
    }

    /**
     * @return null|string
     */
    public function getEmailMessage(): ?string
    {
        return $this->emailMessage;
    }

    /**
     * @param null|string $emailMessage
     */
    public function setEmailMessage(?string $emailMessage): void
    {
        $this->emailMessage = $emailMessage;
    }

    /**
     * @return null|string
     */
    public function getSmsSubject(): ?string
    {
        return $this->smsSubject;
    }

    /**
     * @param null|string $smsSubject
     */
    public function setSmsSubject(?string $smsSubject): void
    {
        $this->smsSubject = $smsSubject;
    }

    /**
     * @return null|string
     */
    public function getSmsMessage(): ?string
    {
        return $this->smsMessage;
    }

    /**
     * @param null|string $smsMessage
     */
    public function setSmsMessage(?string $smsMessage): void
    {
        $this->smsMessage = $smsMessage;
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
     * @return ArrayCollection
     */
    public function getNotifications(): ArrayCollection
    {
        return $this->notifications;
    }

    /**
     * @param ArrayCollection $notifications
     */
    public function setNotifications(ArrayCollection $notifications): void
    {
        $this->notifications = $notifications;
    }
}
