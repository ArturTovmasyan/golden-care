<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class NotificationType
 *
 * @ORM\Entity(repositoryClass="App\Repository\NotificationTypeRepository")
 * @ORM\Table(name="tbl_notification_type")
 * @Grid(
 *     api_notification_type_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "nt.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "at.title",
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

//    /**
//     * @var ArrayCollection
//     * @ORM\OneToMany(targetEntity="App\Entity\Notification", mappedBy="type", cascade={"remove", "persist"})
//     */
//    private $notifications;

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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
}
