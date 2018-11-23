<?php

namespace App\Entity;

use App\Model\Persistence\Entity\ResidentCareTrait;
use App\Model\Persistence\Entity\ResidentStatusTrait;
use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_resident_facility_option")
 * @ORM\Entity(repositoryClass="App\Repository\ResidentFacilityRepository")
 */
class ResidentFacilityOption
{
    use ResidentStatusTrait;
    use ResidentCareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Resident", inversedBy="residentFacilityOption")
     * @ORM\JoinColumn(name="id_resident", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add"
     * })
     */
    private $resident;

    /**
     * @var DiningRoom
     * @ORM\ManyToOne(targetEntity="App\Entity\DiningRoom")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_dining_room", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $diningRoom;

    /**
     * @var FacilityRoom
     * @ORM\ManyToOne(targetEntity="App\Entity\FacilityRoom")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_facility_room", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     * })
     */
    private $facilityRoom;

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
     * @return mixed
     */
    public function getResident()
    {
        return $this->resident;
    }

    /**
     * @param mixed $resident
     */
    public function setResident($resident): void
    {
        $this->resident = $resident;
    }

    /**
     * @return DiningRoom
     */
    public function getDiningRoom(): DiningRoom
    {
        return $this->diningRoom;
    }

    /**
     * @param DiningRoom $diningRoom
     */
    public function setDiningRoom(DiningRoom $diningRoom): void
    {
        $this->diningRoom = $diningRoom;
    }

    /**
     * @return FacilityRoom
     */
    public function getFacilityRoom(): FacilityRoom
    {
        return $this->facilityRoom;
    }

    /**
     * @param FacilityRoom $facilityRoom
     */
    public function setFacilityRoom(FacilityRoom $facilityRoom): void
    {
        $this->facilityRoom = $facilityRoom;
    }
}
