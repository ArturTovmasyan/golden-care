<?php

namespace App\Entity;

use App\Model\Persistence\Entity\PhoneTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_resident_phone")
 * @ORM\Entity(repositoryClass="App\Repository\ResidentPhoneRepository")
 */
class ResidentPhone
{
    use PhoneTrait;

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Resident", inversedBy="phones", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     */
    private $resident;

    /**
     * @return int
     */
    public function getId(): ?int
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
     * @param $resident
     */
    public function setResident($resident): void
    {
        $this->resident = $resident;
    }
}
