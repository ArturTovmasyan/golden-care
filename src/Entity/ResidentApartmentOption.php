<?php

namespace App\Entity;

use App\Model\Persistence\Entity\ResidentStatusTrait;
use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_resident_apartment_option")
 * @ORM\Entity(repositoryClass="App\Repository\ResidentApartmentRepository")
 * @Grid(
 *     api_admin_resident_grid={
 *
 *     }
 * )
 */
class ResidentApartmentOption
{
    use ResidentStatusTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *
     * })
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Resident", inversedBy="residentApartmentOption")
     * @ORM\JoinColumn(name="id_resident", referencedColumnName="id")
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add"
     * })
     */
    private $resident;

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
}
