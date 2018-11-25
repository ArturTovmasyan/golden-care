<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class CareLevel
 *
 * @ORM\Entity(repositoryClass="App\Repository\CareLevelRepository")
 * @UniqueEntity("title", groups={"api_admin_care_level_add", "api_admin_care_level_edit"})
 * @ORM\Table(name="tbl_care_level")
 * @Grid(
 *     api_admin_care_level_grid={
 *          {"id", "number", true, true, "cl.id"},
 *          {"title", "string", true, true, "cl.title"},
 *          {"description", "string", true, true, "cl.description"},
 *     }
 * )
 */
class CareLevel
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_care_level_grid",
     *     "api_admin_care_level_list",
     *     "api_admin_care_level_get",
     *     "api_admin_resident_grid",
     *     "api_admin_resident_list",
     *     "api_admin_resident_get",
     *     "api_admin_resident_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_care_level_add", "api_admin_care_level_edit"})
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_care_level_add", "api_admin_care_level_edit"}
     * )
     * @ORM\Column(name="title", type="string", unique=true, length=255)
     * @Groups({
     *     "api_admin_care_level_grid",
     *     "api_admin_care_level_list",
     *     "api_admin_care_level_get",
     *     "api_admin_resident_get"
     * })
     */
    private $title;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=500, nullable=true)
     * @Assert\Length(
     *      max = 500,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_care_level_add", "api_admin_care_level_edit"}
     * )
     * @Groups({"api_admin_care_level_grid", "api_admin_care_level_list", "api_admin_care_level_get"})
     */
    private $description;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
