<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentResponsiblePerson
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentResponsiblePersonRepository")
 * @ORM\Table(name="tbl_resident_responsible_person")
 * @Grid(
 *     api_admin_resident_responsible_person_grid={
 *          {"id", "number", true, true, "rrp.id"},
 *          {"resident", "string", true, true, "CONCAT(r.firstName, ' ', r.lastName)"},
 *          {"responsible_person", "string", true, true, "CONCAT(rp.firstName, ' ', rp.lastName)"},
 *          {"relationship", "string", true, true, "rel.name"}
 *     }
 * )
 */
class ResidentResponsiblePerson
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Resident",
     *      groups={
     *          "api_admin_resident_responsible_person_add",
     *          "api_admin_resident_responsible_person_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    private $resident;

    /**
     * @var ResponsiblePerson
     * @ORM\ManyToOne(targetEntity="App\Entity\ResponsiblePerson", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_responsible_person", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Responsible Person",
     *      groups={
     *          "api_admin_resident_responsible_person_add",
     *          "api_admin_resident_responsible_person_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     * @Assert\Valid(
     *      groups={
     *          "api_admin_resident_responsible_person_add",
     *          "api_admin_resident_responsible_person_edit"
     *      }
     * )
     */
    private $responsiblePerson;

    /**
     * @ORM\ManyToOne(targetEntity="Relationship", inversedBy="relationshipResidentResponsiblePersons", cascade={"persist"})
     * @ORM\JoinColumn(name="id_relationship", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_responsible_person_add",
     *     "api_admin_resident_responsible_person_edit"
     * })
     * @Groups({
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    private $relationship;

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
     * @return Resident
     */
    public function getResident(): Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident $resident
     */
    public function setResident(Resident $resident): void
    {
        $this->resident = $resident;
    }

    /**
     * @return ResponsiblePerson
     */
    public function getResponsiblePerson(): ResponsiblePerson
    {
        return $this->responsiblePerson;
    }

    /**
     * @param ResponsiblePerson $responsiblePerson
     */
    public function setResponsiblePerson(ResponsiblePerson $responsiblePerson): void
    {
        $this->responsiblePerson = $responsiblePerson;
    }

    /**
     * @return mixed
     */
    public function getRelationship()
    {
        return $this->relationship;
    }

    /**
     * @param mixed $relationship
     */
    public function setRelationship($relationship): void
    {
        $this->relationship = $relationship;
    }
}
