<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use App\Annotation\Grid;

/**
 * Class Salutation
 * @package App\Entity
 *
 * @ORM\Entity(repositoryClass="App\Repository\SalutationRepository")
 * @UniqueEntity("title", groups={"api_admin_salutation_add", "api_admin_salutation_edit"})
 * @ORM\Table(name="tbl_salutation")
 * @Grid(
 *     api_admin_salutation_grid={
 *          {"id", "number", true, true, "s.id"},
 *          {"title", "string", true, true, "s.title"}
 *     }
 * )
 */
class Salutation
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_admin_salutation_list", "api_admin_salutation_get"})
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_salutation_add", "api_admin_salutation_edit"})
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_salutation_add", "api_admin_salutation_edit"}
     * )
     * @ORM\Column(name="title", type="string", unique=true, length=255)
     * @Groups({"api_admin_salutation_grid", "api_admin_salutation_list", "api_admin_salutation_get"})
     */
    private $title;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }

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

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $title = preg_replace('/\s\s+/', ' ', $title);
        $this->title = $title;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("created_at")
     * @Groups({"api_admin_salutation_grid", "api_admin_salutation_list", "api_admin_salutation_get"})
     */
    public function getCreatedAtTime() : string
    {
        return $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : '';
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("updated_at")
     * @Groups({"api_admin_salutation_grid", "api_admin_salutation_list", "api_admin_salutation_get"})
     */
    public function getUpdatedAtTime() : string
    {
        return $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : '';
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("created_by")
     * @Groups({"api_admin_salutation_grid", "api_admin_salutation_list", "api_admin_salutation_get"})
     */
    public function getCreatedById() : int
    {
        return $this->createdBy ? $this->createdBy->getId() : '';
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("updated_by")
     * @Groups({"api_admin_salutation_grid", "api_admin_salutation_list", "api_admin_salutation_get"})
     */
    public function getUpdatedById() : int
    {
        return $this->updatedBy ? $this->updatedBy->getId() : '';
    }
}
