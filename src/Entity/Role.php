<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_role")
 * @ORM\Entity(repositoryClass="App\Repository\RoleRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="This Name is already in use.",
 *     groups={
 *          "api_admin_role_add",
 *          "api_admin_role_edit"
 *     }
 * )
 * @UniqueEntity(
 *     fields={"default"},
 *     repositoryMethod="getRoleByDefaultCriteria",
 *     errorPath="default",
 *     message="Default Role is already in use.",
 *     groups={
 *          "api_admin_role_add",
 *          "api_admin_role_edit"
 *     }
 * )
 * @Grid(
 *     api_admin_role_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "r.id"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "r.name",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "default",
 *              "type"       = "boolean",
 *              "field"      = "r.default"
 *          }
 *     }
 * )
 */
class Role
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     *     "api_admin_user_get",
     *     "api_profile_me",
     *     "api_admin_document_list",
     *     "api_admin_document_get",
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_admin_role_add",
     *     "api_admin_role_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_role_add",
     *          "api_admin_role_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     *     "api_profile_me",
     *     "api_admin_document_list",
     *     "api_admin_corporate_event_list",
     *     "api_admin_corporate_event_get"
     * })
     */
    private $name;

    /**
     * @var bool
     * @ORM\Column(name="is_default", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     *     "api_dashboard_space_role_grid",
     *     "api_dashboard_space_role_list",
     *     "api_dashboard_space_role_get"
     * })
     * @Assert\GreaterThanOrEqual(value=0, groups={
     *     "api_admin_role_add",
     *     "api_admin_role_edit"
     * })
     */
    protected $default;

    /**
     * @var array $grants
     * @ORM\Column(name="grants", type="json_array", nullable=false)
     * @Groups({
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     * })
     */
    private $grants = [];

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="roles", cascade={"persist"})
     */
    private $users;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Document", mappedBy="roles", cascade={"persist"})
     */
    protected $documents;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\CorporateEvent", mappedBy="roles", cascade={"persist"})
     */
    protected $corporateEvents;

    /**
     * Role constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getGrants(): ?array
    {
        return $this->grants;
    }

    /**
     * @param array $grants
     */
    public function setGrants(?array $grants): void
    {
        $this->grants = $grants;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $users
     */
    public function setUsers($users): void
    {
        $this->users = $users;
    }

    /**
     * @return bool
     */
    public function isDefault(): ?bool
    {
        return $this->default;
    }

    /**
     * @param bool $default
     */
    public function setDefault(?bool $default): void
    {
        $this->default = $default;
    }

    /**
     * @return mixed
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param mixed $documents
     */
    public function setDocuments($documents): void
    {
        $this->documents = $documents;

        /** @var Document $document */
        foreach ($this->documents as $document) {
            $document->addRole($this);
        }
    }

    /**
     * @param Document $document
     */
    public function addDocument(Document $document): void
    {
        $document->addRole($this);
        $this->documents[] = $document;
    }

    /**
     * @param Document $document
     */
    public function removeDocument(Document $document): void
    {
        $this->documents->removeElement($document);
        $document->removeRole($this);
    }

    /**
     * @return mixed
     */
    public function getCorporateEvents()
    {
        return $this->corporateEvents;
    }

    /**
     * @param mixed $corporateEvents
     */
    public function setCorporateEvents($corporateEvents): void
    {
        $this->corporateEvents = $corporateEvents;

        /** @var CorporateEvent $corporateEvent */
        foreach ($this->corporateEvents as $corporateEvent) {
            $corporateEvent->addRole($this);
        }
    }

    /**
     * @param CorporateEvent $corporateEvent
     */
    public function addCorporateEvent(CorporateEvent $corporateEvent): void
    {
        $corporateEvent->addRole($this);
        $this->corporateEvents[] = $corporateEvent;
    }

    /**
     * @param CorporateEvent $corporateEvent
     */
    public function removeCorporateEvent(CorporateEvent $corporateEvent): void
    {
        $this->corporateEvents->removeElement($corporateEvent);
        $corporateEvent->removeRole($this);
    }
}
