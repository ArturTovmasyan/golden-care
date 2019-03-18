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
 * Class ResponsiblePersonRole
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResponsiblePersonRoleRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space",
 *     groups={
 *          "api_admin_responsible_person_role_add",
 *          "api_admin_responsible_person_role_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_responsible_person_role")
 * @Grid(
 *     api_admin_responsible_person_role_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rpr.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "rpr.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "icon",
 *              "type"       = "icon",
 *              "field"      = "rpr.icon",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class ResponsiblePersonRole
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_responsible_person_role_list",
     *     "api_admin_responsible_person_role_get",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_responsible_person_role_add",
     *          "api_admin_responsible_person_role_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_admin_responsible_person_role_add",
     *     "api_admin_responsible_person_role_edit"
     * })
     * @Groups({
     *     "api_admin_responsible_person_role_list",
     *     "api_admin_responsible_person_role_get",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(name="icon", type="string", length=255)
     * @Assert\NotNull(groups={
     *     "api_admin_responsible_person_role_add",
     *     "api_admin_responsible_person_role_edit"
     * })
     * @Groups({
     *     "api_admin_responsible_person_role_list",
     *     "api_admin_responsible_person_role_get",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    private $icon;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_responsible_person_role_add",
     *     "api_admin_responsible_person_role_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_responsible_person_role_list",
     *     "api_admin_responsible_person_role_get",
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="ResidentResponsiblePerson", inversedBy="roles", cascade={"persist", "remove"})
     * @ORM\JoinTable(
     *      name="tbl_resident_responsible_person_roles",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_responsible_person_role", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_resident_responsible_person", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     */
    private $residentResponsiblePersons;

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
     * @return string
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     */
    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    public function getSpace(): ?Space
    {
        return $this->space;
    }

    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return mixed
     */
    public function getResidentResponsiblePersons()
    {
        return $this->residentResponsiblePersons;
    }

    /**
     * @param mixed $residentResponsiblePersons
     */
    public function setResidentResponsiblePersons($residentResponsiblePersons): void
    {
        $this->residentResponsiblePersons = $residentResponsiblePersons;

        /** @var ResponsiblePersonRole $residentResponsiblePerson */
        foreach ($this->residentResponsiblePersons as $residentResponsiblePersons) {
            $residentResponsiblePersons->addResidentResponsiblePerson($this);
        }
    }

    /**
     * @param ResidentResponsiblePerson $residentResponsiblePerson
     */
    public function addResidentResponsiblePerson(ResidentResponsiblePerson $residentResponsiblePerson)
    {
        $this->residentResponsiblePersons->add($residentResponsiblePerson);
    }

    /**
     * @param ResidentResponsiblePerson $residentResponsiblePerson
     */
    public function removeResidentResponsiblePerson(ResidentResponsiblePerson $residentResponsiblePerson)
    {
        $this->residentResponsiblePersons->removeElement($residentResponsiblePerson);
    }

}
