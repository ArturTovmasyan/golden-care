<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class Allergen
 *
 * @ORM\Entity(repositoryClass="App\Repository\AllergenRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_allergen_add",
 *          "api_admin_allergen_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_allergen")
 * @Grid(
 *     api_admin_allergen_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "a.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "a.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(a.description, 1, 100)), CASE WHEN LENGTH(a.description) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class Allergen
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_allergen_list",
     *     "api_admin_allergen_get",
     *     "api_admin_resident_allergen_list",
     *     "api_admin_resident_allergen_get",
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_admin_allergen_add",
     *          "api_admin_allergen_edit",
     *          "api_admin_resident_allergen_add",
     *          "api_admin_resident_allergen_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_allergen_add",
     *          "api_admin_allergen_edit",
     *          "api_admin_resident_allergen_add",
     *          "api_admin_resident_allergen_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=200)
     * @Groups({
     *     "api_admin_allergen_grid",
     *     "api_admin_allergen_list",
     *     "api_admin_allergen_get",
     *     "api_admin_resident_allergen_list",
     *     "api_admin_resident_allergen_get",
     * })
     */
    private $title;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=255, nullable=true)
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_allergen_add",
     *          "api_admin_allergen_edit",
     *          "api_admin_resident_allergen_add",
     *          "api_admin_resident_allergen_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_allergen_grid",
     *     "api_admin_allergen_list",
     *     "api_admin_allergen_get"
     * })
     */
    private $description;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_allergen_add",
     *     "api_admin_allergen_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="allergens")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_allergen_grid",
     *     "api_admin_allergen_list",
     *     "api_admin_allergen_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentAllergen", mappedBy="allergen", cascade={"remove", "persist"})
     */
    private $residentAllergens;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $title = preg_replace('/\s\s+/', ' ', $title);
        $this->title = $title;
    }


    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return Space|null
     */
    public function getSpace(): ?Space
    {
        return $this->space;
    }

    /**
     * @param Space|null $space
     */
    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentAllergens(): ArrayCollection
    {
        return $this->residentAllergens;
    }

    /**
     * @param ArrayCollection $residentAllergens
     */
    public function setResidentAllergens(ArrayCollection $residentAllergens): void
    {
        $this->residentAllergens = $residentAllergens;
    }
}
