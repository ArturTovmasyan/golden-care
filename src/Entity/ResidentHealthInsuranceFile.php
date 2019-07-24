<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class ResidentHealthInsuranceFile
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentHealthInsuranceFileRepository")
 * @ORM\Table(name="tbl_resident_health_insurance_file")
 */
class ResidentHealthInsuranceFile
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_health_insurance_file_list",
     *     "api_admin_resident_health_insurance_file_get",
     *     "api_admin_resident_health_insurance_list",
     *     "api_admin_resident_health_insurance_get"
     * })
     */
    private $id;

    /**
     * @var ResidentHealthInsurance
     * @Assert\NotNull(message = "Please select a Health Insurance", groups={
     *     "api_admin_resident_health_insurance_file_add",
     *     "api_admin_resident_health_insurance_file_edit"
     * })
     * @ORM\OneToOne(targetEntity="App\Entity\ResidentHealthInsurance", inversedBy="file")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident_health_insurance", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_health_insurance_file_list",
     *     "api_admin_resident_health_insurance_file_get"
     * })
     */
    private $insurance;

    /**
     * @var string $firstFile
     * @ORM\Column(name="first_file", type="blob", nullable=true)
     */
    private $firstFile;

    /**
     * @var string $secondFile
     * @ORM\Column(name="second_file", type="blob", nullable=true)
     */
    private $secondFile;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("first_file")
     * @Serializer\Groups({"api_admin_resident_health_insurance_get", "api_admin_resident_health_insurance_list"})
     */
    public function getFirst()
    {
        return $this->getFirstFile() !== null ? stream_get_contents($this->getFirstFile()) : null;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("second_file")
     * @Serializer\Groups({"api_admin_resident_health_insurance_get", "api_admin_resident_health_insurance_list"})
     */
    public function getSecond()
    {
        return $this->getSecondFile() !== null ? stream_get_contents($this->getSecondFile()) : null;
    }

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
     * @return ResidentHealthInsurance|null
     */
    public function getInsurance(): ?ResidentHealthInsurance
    {
        return $this->insurance;
    }

    /**
     * @param ResidentHealthInsurance|null $insurance
     */
    public function setInsurance(?ResidentHealthInsurance $insurance): void
    {
        $this->insurance = $insurance;
    }

    public function getFirstFile()
    {
        return $this->firstFile;
    }

    /**
     * @param null|string $firstFile
     */
    public function setFirstFile(?string $firstFile): void
    {
        $this->firstFile = $firstFile;
    }

    public function getSecondFile()
    {
        return $this->secondFile;
    }

    /**
     * @param null|string $secondFile
     */
    public function setSecondFile(?string $secondFile): void
    {
        $this->secondFile = $secondFile;
    }
}
