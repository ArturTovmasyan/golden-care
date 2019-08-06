<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Annotation\Grid as Grid;
use JMS\Serializer\Annotation\Groups;

/**
 * Class Relationship.
 *
 * @ORM\Table(name="tbl_relationship")
 * @ORM\Entity(repositoryClass="App\Repository\RelationshipRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_relationship_add",
 *          "api_admin_relationship_edit"
 *     }
 * )
 * @Grid(
 *     api_admin_relationship_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "r.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "r.title",
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
class Relationship
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_relationship_grid",
     *     "api_admin_relationship_list",
     *     "api_admin_relationship_get",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_relationship_add",
     *     "api_admin_relationship_edit"
     * })
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_relationship_add",
     *          "api_admin_relationship_edit"
     * })
     * @ORM\Column(name="title", type="string", length=50)
     * @Groups({
     *     "api_admin_relationship_grid",
     *     "api_admin_relationship_list",
     *     "api_admin_relationship_get",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_relationship_add",
     *     "api_admin_relationship_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="relationships")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_relationship_grid",
     *     "api_admin_relationship_list",
     *     "api_admin_relationship_get"
     * })
     */
    private $space;

    /**
     * @ORM\OneToMany(targetEntity="ResidentResponsiblePerson", mappedBy="relationship", cascade={"persist", "remove"})
     */
    protected $residentResponsiblePersons;

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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
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
    }
}
