<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentPhysician
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentPhysicianRepository")
 * @ORM\Table(name="tbl_resident_physician")
 * @Grid(
 *     api_admin_resident_physician_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rp.id"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(ps.title,''), ' ', COALESCE(p.firstName, ''), ' ', COALESCE(p.middleName, ''), ' ', COALESCE(p.lastName, ''))",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "primary",
 *              "type"       = "boolean",
 *              "field"      = "rp.primary"
 *          }
 *     }
 * )
 */
class ResidentPhysician
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
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
     *          "api_admin_resident_physician_add",
     *          "api_admin_resident_physician_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $resident;

    /**
     * @var Physician
     * @ORM\ManyToOne(targetEntity="App\Entity\Physician", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_physician", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Physician",
     *      groups={
     *          "api_admin_resident_physician_add",
     *          "api_admin_resident_physician_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     * @Assert\Valid(
     *      groups={
     *          "api_admin_resident_physician_add",
     *          "api_admin_resident_physician_edit"
     *      }
     * )
     */
    private $physician;

    /**
     * @var bool
     * @ORM\Column(name="is_primary", type="boolean", nullable=false)
     * @Assert\NotNull(groups={
     *     "api_admin_resident_physician_add",
     *     "api_admin_resident_physician_edit"
     * })
     * @Groups({
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $primary = false;

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
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->primary;
    }

    /**
     * @param bool $primary
     */
    public function setPrimary(bool $primary): void
    {
        $this->primary = $primary;
    }
}
