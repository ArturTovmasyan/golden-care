<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class Diet
 *
 * @ORM\Entity(repositoryClass="App\Repository\DietRepository")
 * @ORM\Table(name="tbl_diet")
 * @Grid(
 *     api_admin_diet_grid={
 *          {"id", "number", true, true, "d.id"},
 *          {"title", "string", true, true, "d.title"},
 *          {"color", "string", true, true, "d.color"}
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
     * @Assert\NotBlank(groups={"api_admin_diet_add", "api_admin_diet_edit"})
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_diet_add", "api_admin_diet_edit"}
     * )
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
     * @Assert\NotBlank(groups={"api_admin_diet_add", "api_admin_diet_edit"})
     * @Assert\Length(
     *      max = 20,
     *      maxMessage = "Color cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_diet_add", "api_admin_diet_edit"}
     * )
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

    public function getId(): int
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
}