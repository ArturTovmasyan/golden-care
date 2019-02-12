<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class Speciality
 *
 * @ORM\Entity(repositoryClass="App\Repository\SpecialityRepository")
 * @ORM\Table(name="tbl_speciality")
 * @Grid(
 *     api_admin_speciality_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "sp.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "sp.title"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class Speciality
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_speciality_list",
     *     "api_admin_speciality_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_speciality_add", "api_admin_speciality_edit"}
     * )
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_admin_speciality_add",
     *     "api_admin_speciality_edit"
     * })
     * @Groups({
     *     "api_admin_speciality_list",
     *     "api_admin_speciality_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_speciality_add",
     *     "api_admin_speciality_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $space;

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

    public function getSpace(): ?Space
    {
        return $this->space;
    }

    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }
}
