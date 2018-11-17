<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
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
 *          {"id", "number", true, true, "r.id"},
 *          {"name", "string", true, true, "r.name"},
 *          {"description", "string", true, true, "r.description"},
 *          {"shorthand", "string", true, true, "r.shorthand"},
 *          {"phone", "string", true, true, "r.phone"},
 *          {"fax", "string", true, true, "r.fax"}
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
     * @Groups({"api_admin_region_list", "api_admin_region_get"})
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_region_add", "api_admin_region_edit"})
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_region_add", "api_admin_region_edit"}
     * )
     * @ORM\Column(name="name", type="string", length=100)
     * @Groups({"api_admin_region_grid", "api_admin_region_list", "api_admin_region_get"})
     */
    private $name;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=1000, nullable=true)
     * @Assert\Length(
     *      max = 1000,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_region_add", "api_admin_region_edit"}
     * )
     * @Groups({"api_admin_region_grid", "api_admin_region_list", "api_admin_region_get"})
     */
    private $description;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_region_add", "api_admin_region_edit"})
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Shorthand cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_region_add", "api_admin_region_edit"}
     * )
     * @ORM\Column(name="shorthand", type="string", length=100)
     * @Groups({"api_admin_region_grid", "api_admin_region_list", "api_admin_region_get"})
     */
    private $shorthand;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\(\d{3}\) \d{3}-\d{4}$/",
     *     message="Invalid Phone number. Should be like '(916) 727-4232'",
     *     groups={"api_admin_region_add", "api_admin_region_edit"}
     * )
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     * @Groups({"api_admin_region_grid", "api_admin_region_list", "api_admin_region_get"})
     */
    private $phone;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\(\d{3}\) \d{3}-\d{4}$/",
     *     message="Invalid Fax number. Should be like '(916) 727-4232'",
     *     groups={"api_admin_region_add", "api_admin_region_edit"}
     * )
     * @ORM\Column(name="fax", type="string", length=20, nullable=true)
     * @Groups({"api_admin_region_grid", "api_admin_region_list", "api_admin_region_get"})
     */
    private $fax;

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
}
