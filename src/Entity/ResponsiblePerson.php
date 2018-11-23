<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_responsible_person")
 * @ORM\Entity(repositoryClass="App\Repository\ResponsiblePersonRepository")
 */
class ResponsiblePerson
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_responsible_person_grid",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=40, nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_admin_responsible_person_edit",
     *     "api_admin_responsible_person_add"
     * })
     * @Groups({
     *     "api_admin_responsible_person_grid",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get"
     * })
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=40, nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_admin_responsible_person_edit",
     *     "api_admin_responsible_person_add"
     * })
     * @Groups({
     *     "api_admin_responsible_person_grid",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get"
     * })
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(name="middle_name", type="string", length=40, nullable=true)
     * @Groups({
     *     "api_admin_responsible_person_grid",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get"
     * })
     */
    private $middleName;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *
     * })
     * @ORM\Column(name="address_1", type="string", length=100, nullable=false)
     * @Groups({
     *     "api_admin_responsible_person_grid",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get"
     * })
     */
    private $address_1;

    /**
     * @var string
     *
     * @ORM\Column(name="address_2", type="string", length=100, nullable=true)
     * @Groups({
     *     "api_admin_responsible_person_grid",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get"
     * })
     */
    private $address_2;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Groups({
     *     "api_admin_responsible_person_grid",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get"
     * })
     * @Assert\Email(
     *     groups={
     *
     *     }
     * )
     */
    private $email;

    /**
     * @var bool
     * @ORM\Column(name="is_financially", type="boolean", nullable=false)
     * @Assert\NotNull(groups={
     *
     * })
     * @Groups({
     *     "api_admin_responsible_person_grid",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get"
     * })
     */
    private $financially = false;

    /**
     * @var bool
     * @ORM\Column(name="is_emergency", type="boolean", nullable=false)
     * @Groups({
     *     "api_admin_responsible_person_grid",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get"
     * })
     */
    private $emergency = false;

    /**
     * @var CityStateZip
     * @ORM\ManyToOne(targetEntity="CityStateZip")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Assert\NotBlank(groups={
     *
     * })
     * @Groups({
     *     "api_admin_responsible_person_grid",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get"
     * })
     */
    private $csz;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={"api_admin_responsible_person_add", "api_responsible_person_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_admin_responsible_person_grid",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_get"
     * })
     */
    private $space;

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
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     */
    public function setMiddleName(string $middleName): void
    {
        $this->middleName = $middleName;
    }

    /**
     * @return string
     */
    public function getAddress1(): string
    {
        return $this->address_1;
    }

    /**
     * @param string $address_1
     */
    public function setAddress1(string $address_1): void
    {
        $this->address_1 = $address_1;
    }

    /**
     * @return string
     */
    public function getAddress2(): string
    {
        return $this->address_2;
    }

    /**
     * @param string $address_2
     */
    public function setAddress2(string $address_2): void
    {
        $this->address_2 = $address_2;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isFinancially(): bool
    {
        return $this->financially;
    }

    /**
     * @param bool $financially
     */
    public function setFinancially(bool $financially): void
    {
        $this->financially = $financially;
    }

    /**
     * @return bool
     */
    public function isEmergency(): bool
    {
        return $this->emergency;
    }

    /**
     * @param bool $emergency
     */
    public function setEmergency(bool $emergency): void
    {
        $this->emergency = $emergency;
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
     * @return Space
     */
    public function getSpace(): Space
    {
        return $this->space;
    }

    /**
     * @param Space $space
     */
    public function setSpace(Space $space): void
    {
        $this->space = $space;
    }
}