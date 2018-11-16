<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class Facility
 *
 * @ORM\Entity(repositoryClass="App\Repository\FacilityRepository")
 * @ORM\Table(name="tbl_facility")
 * @Grid(
 *     api_admin_facility_grid={
 *          {"id", "number", true, true, "f.id"},
 *          {"name", "string", true, true, "f.name"},
 *          {"description", "string", true, true, "f.description"},
 *          {"shorthand", "string", true, true, "f.shorthand"},
 *          {"phone", "string", true, true, "f.phone"},
 *          {"fax", "string", true, true, "f.fax"},
 *          {"address1", "string", true, true, "f.address1"},
 *          {"license", "string", true, true, "f.license"},
 *          {"csz_id", "number", true, true, "csz.id"},
 *          {"csz_city", "string", true, true, "csz.city"},
 *          {"csz_state_abbr", "string", true, true, "csz.stateAbbr"},
 *          {"csz_zip_main", "string", true, true, "csz.zipMain"},
 *          {"max_beds_number", "string", true, true, "f.maxBedsNumber"},
 *     }
 * )
 */
class Facility
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_admin_facility_list", "api_admin_facility_get"})
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_facility_add", "api_admin_facility_edit"})
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @ORM\Column(name="name", type="string", length=100)
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $name;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=1000, nullable=true)
     * @Assert\Length(
     *      max = 1000,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $description;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_facility_add", "api_admin_facility_edit"})
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Shorthand cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @ORM\Column(name="shorthand", type="string", length=100)
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $shorthand;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\(\d{3}\) \d{3}-\d{4}$/",
     *     message="Invalid Phone number. Should be like '(916) 727-4232'",
     *     groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $phone;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\(\d{3}\) \d{3}-\d{4}$/",
     *     message="Invalid Fax number. Should be like '(916) 727-4232'",
     *     groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @ORM\Column(name="fax", type="string", length=20, nullable=true)
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $fax;
    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_facility_add", "api_admin_facility_edit"})
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Address 1 cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @ORM\Column(name="address1", type="string", length=100)
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $address1;

    /**
     * @var string $license
     * @ORM\Column(name="license", type="string", length=20, nullable=true)
     * @Assert\Length(
     *      max = 20,
     *      maxMessage = "License cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $license;

    /**
     * @var CityStateZip
     * @Assert\NotNull(message = "Please select a City State & Zip", groups={"api_admin_facility_add", "api_admin_facility_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\CityStateZip")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $csz;

    /**
     * @var int
     * @Assert\NotBlank(groups={"api_admin_facility_add", "api_admin_facility_edit"})
     * @Assert\Regex(
     *      pattern="/(^[1-9][0-9]*$)/",
     *      message="The value should be numeric",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @ORM\Column(name="max_beds_number", type="integer")
     * @Groups({"api_admin_facility_grid", "api_admin_facility_list", "api_admin_facility_get"})
     */
    private $maxBedsNumber;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getShorthand(): ?string
    {
        return $this->shorthand;
    }

    public function setShorthand(string $shorthand): self
    {
        $this->shorthand = $shorthand;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function setAddress1(string $address1): self
    {
        $this->address1 = $address1;

        return $this;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function setLicense(?string $license): self
    {
        $this->license = $license;

        return $this;
    }

    public function getMaxBedsNumber(): ?int
    {
        return $this->maxBedsNumber;
    }

    public function setMaxBedsNumber($maxBedsNumber): self
    {
        $this->maxBedsNumber = $maxBedsNumber;

        return $this;
    }

    public function getCsz(): ?CityStateZip
    {
        return $this->csz;
    }

    public function setCsz(?CityStateZip $csz): self
    {
        $this->csz = $csz;

        return $this;
    }
}
