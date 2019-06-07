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
 * Class Notification
 *
 * @ORM\Entity(repositoryClass="App\Repository\NotificationRepository")
 * @ORM\Table(name="tbl_notification")
 * @Grid(
 *     api_admin_notification_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "nt.id"
 *          },
 *          {
 *              "id"         = "type",
 *              "type"       = "string",
 *              "field"      = "nt.title"
 *          }
 *     }
 * )
 */
class Notification
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $id;

    /**
     * @var NotificationType
     * @Assert\NotNull(message = "Please select a Type", groups={
     *     "api_admin_notification_add",
     *     "api_admin_notification_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\NotificationType", inversedBy="notifications")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_type", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $type;

    /**
     * @var array $schedule
     * @ORM\Column(name="schedule", type="json_array", nullable=true)
     * @Assert\Count(
     *      max = 10,
     *      maxMessage = "You cannot specify more than {{ limit }} schedules",
     *      groups={
     *          "api_admin_notification_add",
     *          "api_admin_notification_edit"
     * })
     * @Groups({
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $schedule = [];

    /**
     * @var array $parameters
     * @ORM\Column(name="parameters", type="json_array", nullable=true)
     * @Assert\Count(
     *      max = 10,
     *      maxMessage = "You cannot specify more than {{ limit }} parameters",
     *      groups={
     *          "api_admin_notification_add",
     *          "api_admin_notification_edit"
     * })
     * @Groups({
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $parameters = [];

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="notifications", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_notification_users",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_notification", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $users;

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return NotificationType|null
     */
    public function getType(): ?NotificationType
    {
        return $this->type;
    }

    /**
     * @param NotificationType|null $type
     */
    public function setType(?NotificationType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getSchedule(): array
    {
        return $this->schedule;
    }

    /**
     * @param array $schedule
     */
    public function setSchedule(array $schedule): void
    {
        $this->schedule = $schedule;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
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
}
