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
 * @ORM\Table(name="tbl_resident_region_option")
 * @ORM\Entity(repositoryClass="App\Repository\ResidentRegionRepository")
 */
class ResidentRegionOption
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
     * @ORM\OneToOne(targetEntity="App\Entity\Resident", inversedBy="residentRegionOption")
     * @ORM\JoinColumn(name="id_resident", referencedColumnName="id")
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add"
     * })
     */
    private $resident;

    /**
     * @var Region
     * @ORM\ManyToOne(targetEntity="Region")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_region", referencedColumnName="id", onDelete="SET NULL")
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
    private $region;

    /**
     * @var CityStateZip
     * @ORM\ManyToOne(targetEntity="CityStateZip")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     */
    private $csz;

    /**
     * @var string
     * @ORM\Column(name="street_address", type="string", length=200, nullable=true)
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     */
    private $streetAddress;

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
     * @return Region
     */
    public function getRegion(): Region
    {
        return $this->region;
    }

    /**
     * @param Region $region
     */
    public function setRegion(Region $region): void
    {
        $this->region = $region;
    }

    /**
     * @return CityStateZip
     */
    public function getCsz(): CityStateZip
    {
        return $this->csz;
    }

    /**
     * @param CityStateZip $csz
     */
    public function setCsz(CityStateZip $csz): void
    {
        $this->csz = $csz;
    }

    /**
     * @return string
     */
    public function getStreetAddress(): string
    {
        return $this->streetAddress;
    }

    /**
     * @param string $streetAddress
     */
    public function setStreetAddress(string $streetAddress): void
    {
        $this->streetAddress = $streetAddress;
    }
}
