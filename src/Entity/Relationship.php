<?php

namespace App\Entity;

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
 * @UniqueEntity(fields="name", message="Sorry, this name is already in use.", groups={"api_admin_relationship_add", "api_admin_relationship_edit"})
 * @Grid(
 *     api_admin_relationship_grid={
 *          {"id", "number", true, true, "r.id"},
 *          {"name", "string", true, true, "r.name"}
 *          {"space", "string", true, true, "s.name"},
 *     },
 *     api_dashboard_relationship_grid={
 *          {"id", "number", true, true, "r.id"},
 *          {"name", "string", true, true, "r.name"}
 *          {"space", "string", true, true, "s.name"},
 *     }
 * )
 */
class Relationship
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_relationship_grid",
     *     "api_admin_relationship_list",
     *     "api_admin_relationship_get",
     *     "api_dashboard_relationship_grid",
     *     "api_dashboard_relationship_list",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_relationship_add", "api_admin_relationship_edit"})
     * @ORM\Column(name="name", type="string", length=20, nullable=false)
     * @Groups({
     *     "api_admin_relationship_grid",
     *     "api_admin_relationship_list",
     *     "api_admin_relationship_get",
     *     "api_dashboard_relationship_grid",
     *     "api_dashboard_relationship_list",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    private $name;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={"api_admin_relationship_add", "api_admin_relationship_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="SET NULL")
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
    protected $relationshipResidentResponsiblePersons;

    /**
     * @return int
     */
    public function getId(): int
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
     * @return Space|null
     */
    public function getSpace(): ?Space
    {
        return $this->space;
    }

    /**
     * @param Space|null $space
     * @return Relationship
     */
    public function setSpace(?Space $space): self
    {
        $this->space = $space;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRelationshipResidentResponsiblePersons()
    {
        return $this->relationshipResidentResponsiblePersons;
    }

    /**
     * @param mixed $relationshipResidentResponsiblePersons
     */
    public function setRelationshipResidentResponsiblePersons($relationshipResidentResponsiblePersons): void
    {
        $this->relationshipResidentResponsiblePersons = $relationshipResidentResponsiblePersons;
    }
}
