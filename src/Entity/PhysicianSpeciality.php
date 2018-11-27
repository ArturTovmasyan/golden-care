<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class PhysicianSpeciality
 *
 * @ORM\Entity(repositoryClass="App\Repository\PhysicianSpecialityRepository")
 * @ORM\Table(name="tbl_physician_speciality")
 * @Grid(
 *      api_admin_physician_speciality_grid={
 *          {"id", "number", true, true, "ps.id"},
 *          {"physician", "string", true, true, "CONCAT(p.firstName, ' ', p.lastName)"},
 *          {"speciality", "string", true, true, "s.title"}
 *      }
 * )
 */
class PhysicianSpeciality
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_physician_speciality_list",
     *     "api_admin_physician_speciality_get"
     * })
     */
    private $id;

    /**
     * @var Physician
     * @ORM\ManyToOne(targetEntity="Physician")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_physician", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Physician", groups={
     *     "api_admin_physician_speciality_add",
     *     "api_admin_physician_speciality_edit"
     * })
     * @Groups({"api_admin_physician_speciality_grid", "api_admin_physician_speciality_list", "api_admin_physician_speciality_get"})
     */
    private $physician;

    /**
     * @var Speciality
     * @ORM\ManyToOne(targetEntity="Speciality", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_speciality", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Speciality", groups={
     *     "api_admin_physician_speciality_add", 
     *     "api_admin_physician_speciality_edit"
     * })
     * @Assert\Valid(groups={
     *     "api_admin_physician_speciality_add", 
     *     "api_admin_physician_speciality_edit"
     * })
     * @Groups({
     *     "api_admin_physician_speciality_list", 
     *     "api_admin_physician_speciality_get"
     * })
     */
    private $speciality;

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
     * @return Physician
     */
    public function getPhysician(): Physician
    {
        return $this->physician;
    }

    /**
     * @param Physician $physician
     */
    public function setPhysician(Physician $physician): void
    {
        $this->physician = $physician;
    }

    /**
     * @return Speciality
     */
    public function getSpeciality(): Speciality
    {
        return $this->speciality;
    }

    /**
     * @param Speciality $speciality
     */
    public function setSpeciality(Speciality $speciality): void
    {
        $this->speciality = $speciality;
    }
}
