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
 * Class Diet
 *
 * @ORM\Entity(repositoryClass="App\Repository\DietRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space.",
 *     groups={
 *          "api_admin_diet_add",
 *          "api_admin_diet_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_diet")
 * @Grid(
 *     api_admin_diet_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "d.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "d.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "color",
 *              "type"       = "color",
 *              "field"      = "d.color"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class Diet
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_diet_list",
     *     "api_admin_diet_get",
     *     "api_admin_resident_diet_list",
     *     "api_admin_resident_diet_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_diet_add",
     *     "api_admin_diet_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_diet_add",
     *          "api_admin_diet_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_diet_grid",
     *     "api_admin_diet_list",
     *     "api_admin_diet_get",
     *     "api_admin_resident_diet_list",
     *     "api_admin_resident_diet_get"
     * })
     */
    private $title;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_diet_add",
     *     "api_admin_diet_edit"
     * })
     * @Assert\Length(
     *      max = 20,
     *      maxMessage = "Color cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_diet_add",
     *          "api_admin_diet_edit"
     * })
     * @ORM\Column(name="color", type="string", length=20)
     * @Groups({
     *     "api_admin_diet_grid",
     *     "api_admin_diet_list",
     *     "api_admin_diet_get",
     *     "api_admin_resident_diet_list",
     *     "api_admin_resident_diet_get"
     * })
     */
    private $color;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_diet_add",
     *     "api_admin_diet_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="diets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_diet_grid",
     *     "api_admin_diet_list",
     *     "api_admin_diet_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentDiet", mappedBy="diet", cascade={"remove", "persist"})
     */
    private $residentDiets;

    public function getId()
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
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
    public function getResidentDiets(): ArrayCollection
    {
        return $this->residentDiets;
    }

    /**
     * @param ArrayCollection $residentDiets
     */
    public function setResidentDiets(ArrayCollection $residentDiets): void
    {
        $this->residentDiets = $residentDiets;
    }
}
