<?php

namespace App\Entity;

use App\Model\Persistence\Entity\PhoneTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_physician_phone")
 * @ORM\Entity(repositoryClass="App\Repository\PhysicianPhoneRepository")
 */
class PhysicianPhone
{
    use PhoneTrait;

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups({
     *      "api_admin_physician_list",
     *      "api_admin_physician_get",
     *      "api_admin_resident_physician_list"
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Physician", inversedBy="phones", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_physician", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     */
    private $physician;

    /**
     * @ORM\Column(name="extension", type="integer", nullable=true)
     * @Assert\Range(
     *      min = 0,
     *      max = 99999,
     *      groups={
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     * })
     * @Groups({
     *      "api_admin_physician_list",
     *      "api_admin_physician_get",
     *      "api_admin_resident_physician_list"
     * })
     */
    private $extension;

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
    public function getPhysician()
    {
        return $this->physician;
    }

    /**
     * @param mixed $physician
     */
    public function setPhysician($physician): void
    {
        $this->physician = $physician;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     */
    public function setExtension($extension): void
    {
        $this->extension = $extension;
    }
}
