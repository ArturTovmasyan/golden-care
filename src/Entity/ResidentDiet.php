<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentDiet
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentDietRepository")
 * @UniqueEntity(
 *     fields={"resident", "diet"},
 *     errorPath="diet_id",
 *     message="The value is already in use for this Resident.",
 *     groups={
 *          "api_admin_resident_diet_add",
 *          "api_admin_resident_diet_edit"
 *     }
 * )
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
 *              "field"      = "d.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "diet_color",
 *              "type"       = "color",
 *              "field"      = "d.color"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(rd.description, 1, 100)), CASE WHEN LENGTH(rd.description) > 100 THEN '…' ELSE '' END)"
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
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_diet_add",
     *     "api_admin_resident_diet_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentDiets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_diet_grid",
     *     "api_admin_resident_diet_list",
     *     "api_admin_resident_diet_get"
     * })
     */
    private $resident;

    /**
     * @var Diet
     * @Assert\NotNull(message = "Please select a Diet", groups={
     *     "api_admin_resident_diet_add",
     *     "api_admin_resident_diet_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Diet", inversedBy="residentDiets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_diet", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_diet_grid",
     *     "api_admin_resident_diet_list",
     *     "api_admin_resident_diet_get"
     * })
     */
    private $diet;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=512)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_diet_add",
     *     "api_admin_resident_diet_edit"
     * })
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_diet_add",
     *          "api_admin_resident_diet_edit"
     * })
     * @Groups({
     *     "api_admin_resident_diet_grid",
     *     "api_admin_resident_diet_list",
     *     "api_admin_resident_diet_get"
     * })
     */
    private $description;

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
     * @return Resident|null
     */
    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident|null $resident
     */
    public function setResident(?Resident $resident): void
    {
        $this->resident = $resident;
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
     */
    public function setDiet(?Diet $diet): void
    {
        $this->diet = $diet;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
