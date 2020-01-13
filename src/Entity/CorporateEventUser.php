<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CorporateEventUserRepository")
 * @UniqueEntity(
 *     fields={"event", "user"},
 *     errorPath="user",
 *     message="The user is already in use for this Corporate Event.",
 *     groups={
 *          "api_admin_facility_document_add",
 *          "api_admin_facility_document_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_corporate_event_user")
 */
class CorporateEventUser
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    private $id;

    /**
     * @var CorporateEvent
     * @ORM\ManyToOne(targetEntity="App\Entity\CorporateEvent", inversedBy="corporateEventUsers", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_corporate_event", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Corporate Event",
     *      groups={
     *          "api_admin_corporate_event_user_edit",
     *          "api_admin_corporate_event_user_add"
     *      }
     * )
     */
    private $event;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="corporateEventUsers", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select an User",
     *      groups={
     *          "api_admin_corporate_event_user_edit",
     *          "api_admin_corporate_event_user_add"
     *      }
     * )
     * @Groups({
     *      "api_admin_corporate_event_list",
     *      "api_admin_corporate_event_get"
     * })
     */
    private $user;

    /**
     * @var bool
     * @ORM\Column(name="done", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    protected $done;

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
     * @return CorporateEvent|null
     */
    public function getEvent(): ?CorporateEvent
    {
        return $this->event;
    }

    /**
     * @param CorporateEvent|null $event
     */
    public function setEvent(?CorporateEvent $event): void
    {
        $this->event = $event;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return bool
     */
    public function isDone(): bool
    {
        return $this->done;
    }

    /**
     * @param bool $done
     */
    public function setDone(bool $done): void
    {
        $this->done = $done;
    }
}
