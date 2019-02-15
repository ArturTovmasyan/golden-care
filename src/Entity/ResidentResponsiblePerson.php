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
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rrp.id"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(rp.firstName, ' ', rp.middleName, ' ', rp.lastName)",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "relationship",
 *              "type"       = "string",
 *              "field"      = "rel.title"
 *          },
 *          {
 *              "id"         = "address",
 *              "type"       = "string",
 *              "field"      = "CONCAT(rp.address1, ' ', rp.address2)"
 *          }
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
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_relationship", referencedColumnName="id", onDelete="CASCADE")
     * })
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
