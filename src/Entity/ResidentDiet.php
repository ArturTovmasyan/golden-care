<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentDiet
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentDietRepository")
 * @ORM\Table(name="tbl_resident_diet")
 * @Grid(
 *     api_admin_resident_diet_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rd.id"
 *          },
 *          {
 *              "id"         = "diet_title",
 *              "type"       = "string",
 *              "field"      = "d.title"
 *          },
 *          {
 *              "id"         = "diet_color",
 *              "type"       = "string",
 *              "field"      = "d.color"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "rd.description"
 *          }
 *     }
 * )
 */
class ResidentDiet
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_diet_grid",
     *     "api_admin_resident_diet_list",
     *     "api_admin_resident_diet_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={"api_admin_resident_diet_add", "api_admin_resident_diet_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_diet_grid", "api_admin_resident_diet_list", "api_admin_resident_diet_get"})
     */
    private $resident;

    /**
     * @var Diet
     * @Assert\NotNull(message = "Please select a Diet", groups={"api_admin_resident_diet_add", "api_admin_resident_diet_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Diet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_diet", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_diet_grid", "api_admin_resident_diet_list", "api_admin_resident_diet_get"})
     */
    private $diet;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=512)
     * @Assert\NotBlank(groups={"api_admin_resident_diet_add", "api_admin_resident_diet_edit"})
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_diet_add", "api_admin_resident_diet_edit"}
     * )
     * @Groups({"api_admin_resident_diet_grid", "api_admin_resident_diet_list", "api_admin_resident_diet_get"})
     */
    private $description;

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
     * @return Resident|null
     */
    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident|null $resident
     * @return ResidentDiet
     */
    public function setResident(?Resident $resident): self
    {
        $this->resident = $resident;

        return $this;
    }

    /**
     * @return Diet|null
     */
    public function getDiet(): ?Diet
    {
        return $this->diet;
    }

    /**
     * @param Diet|null $diet
     * @return ResidentDiet
     */
    public function setDiet(?Diet $diet): self
    {
        $this->diet = $diet;

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
}
