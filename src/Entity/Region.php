<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class Region
 *
 * @ORM\Entity(repositoryClass="App\Repository\RegionRepository")
 * @ORM\Table(name="tbl_region")
 * @Grid(
 *     api_admin_region_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "r.id"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "r.name",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "r.description"
 *          },
 *          {
 *              "id"         = "shorthand",
 *              "type"       = "string",
 *              "field"      = "r.shorthand"
 *          },
 *          {
 *              "id"         = "phone",
 *              "type"       = "string",
 *              "field"      = "r.phone"
 *          },
 *          {
 *              "id"         = "fax",
 *              "type"       = "string",
 *              "field"      = "r.fax"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class Region
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_region_list",
     *     "api_admin_region_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_region_add",
     *     "api_admin_region_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_region_add",
     *          "api_admin_region_edit"
     * })
     * @ORM\Column(name="name", type="string", length=100)
     * @Groups({
     *     "api_admin_region_grid",
     *     "api_admin_region_list",
     *     "api_admin_region_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $name;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=1000, nullable=true)
     * @Assert\Length(
     *      max = 1000,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={
     *         "api_admin_region_add",
     *         "api_admin_region_edit"
     * })
     * @Groups({
     *     "api_admin_region_grid",
     *     "api_admin_region_list",
     *     "api_admin_region_get"
     * })
     */
    private $description;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_region_add",
     *     "api_admin_region_edit"
     * })
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Shorthand cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_region_add",
     *          "api_admin_region_edit"
     * })
     * @ORM\Column(name="shorthand", type="string", length=100)
     * @Groups({
     *     "api_admin_region_grid",
     *     "api_admin_region_list",
     *     "api_admin_region_get"
     * })
     */
    private $shorthand;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\([0-9]{3}\)\s?[0-9]{3}-[0-9]{4}$/",
     *     message="Invalid phone number format. Valid format is (XXX) XXX-XXXX.",
     *     groups={
     *          "api_admin_region_add",
     *          "api_admin_region_edit"
     * })
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     * @Groups({
     *     "api_admin_region_grid",
     *     "api_admin_region_list",
     *     "api_admin_region_get"
     * })
     */
    private $phone;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\([0-9]{3}\)\s?[0-9]{3}-[0-9]{4}$/",
     *     message="Invalid fax number format. Valid format is (XXX) XXX-XXXX.",
     *     groups={
     *          "api_admin_region_add",
     *          "api_admin_region_edit"
     * })
     * @ORM\Column(name="fax", type="string", length=20, nullable=true)
     * @Groups({
     *     "api_admin_region_grid",
     *     "api_admin_region_list",
     *     "api_admin_region_get"
     * })
     */
    private $fax;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_region_add",
     *     "api_admin_region_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_region_grid",
     *     "api_admin_region_list",
     *     "api_admin_region_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentAdmission", mappedBy="region", cascade={"remove", "persist"})
     */
    private $residentAdmissions;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getShorthand(): ?string
    {
        return $this->shorthand;
    }

    public function setShorthand(string $shorthand): void
    {
        $this->shorthand = $shorthand;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): void
    {
        $this->fax = $fax;
    }

    public function getSpace(): ?Space
    {
        return $this->space;
    }

    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentAdmissions(): ArrayCollection
    {
        return $this->residentAdmissions;
    }

    /**
     * @param ArrayCollection $residentAdmissions
     */
    public function setResidentAdmissions(ArrayCollection $residentAdmissions): void
    {
        $this->residentAdmissions = $residentAdmissions;
    }
}
