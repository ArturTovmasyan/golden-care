<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
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
 *              "field"      = "n.id"
 *          },
 *          {
 *              "id"         = "type",
 *              "type"       = "string",
 *              "field"      = "nt.title"
 *          },
 *          {
 *              "id"         = "enabled",
 *              "type"       = "boolean",
 *              "field"      = "n.enabled"
 *          },
 *          {
 *              "id"         = "schedule",
 *              "type"       = "cron",
 *              "field"      = "n.schedule"
 *          },
 *          {
 *              "id"         = "emails",
 *              "type"       = "string",
 *              "field"      = "n.emails"
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
     * @var bool
     * @ORM\Column(name="enabled", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $enabled;

    /**
     * @var string
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "Schedule cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_notification_add",
     *          "api_admin_notification_edit"
     *      }
     * )
     * @ORM\Column(name="schedule", type="string", length=120, nullable=true)
     * @Groups({
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $schedule;

    /**
     * @var array $emails
     * @ORM\Column(name="emails", type="json_array", nullable=true)
     * @Assert\Count(
     *      max = 10,
     *      maxMessage = "You cannot specify more than {{ limit }} emails",
     *      groups={
     *          "api_admin_notification_add",
     *          "api_admin_notification_edit"
     * })
     * @Groups({
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $emails = [];

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

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Facility", inversedBy="notifications", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_notification_facilities",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_notification", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $facilities;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Apartment", inversedBy="notifications", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_notification_apartments",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_notification", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_apartment", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $apartments;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Region", inversedBy="notifications", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_notification_regions",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_notification", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_region", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_admin_notification_list",
     *     "api_admin_notification_get"
     * })
     */
    private $regions;

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
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return null|string
     */
    public function getSchedule(): ?string
    {
        return $this->schedule;
    }

    /**
     * @param null|string $schedule
     */
    public function setSchedule(?string $schedule): void
    {
        $this->schedule = $schedule;
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
     * @return mixed
     */
    public function getApartments()
    {
        return $this->apartments;
    }

    /**
     * @param $apartments
     */
    public function setApartments($apartments): void
    {
        $this->apartments = $apartments;
    }

    /**
     * @param Apartment|null $apartment
     */
    public function addApartment(?Apartment $apartment): void
    {
        $this->apartments->add($apartment);
    }

    /**
     * @param Apartment|null $apartment
     */
    public function removeApartment(?Apartment $apartment): void
    {
        $this->apartments->removeElement($apartment);
    }

    /**
     * @return mixed
     */
    public function getRegions()
    {
        return $this->regions;
    }

    /**
     * @param $regions
     */
    public function setRegions($regions): void
    {
        $this->regions = $regions;
    }

    /**
     * @param Region|null $region
     */
    public function addRegion(?Region $region): void
    {
        $this->regions->add($region);
    }

    /**
     * @param Region|null $region
     */
    public function removeRegion(?Region $region): void
    {
        $this->regions->removeElement($region);
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback(groups={
     *     "api_admin_notification_add",
     *     "api_admin_notification_edit"
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
