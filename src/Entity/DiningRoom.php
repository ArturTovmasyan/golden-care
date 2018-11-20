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
 * Class DiningRoom
 *
 * @ORM\Entity(repositoryClass="App\Repository\DiningRoomRepository")
 * @UniqueEntity("title", groups={"api_admin_dining_room_add", "api_admin_dining_room_edit"})
 * @ORM\Table(name="tbl_dining_room")
 * @Grid(
 *     api_admin_dining_room_grid={
 *          {"id", "number", true, true, "dr.id"},
 *          {"title", "string", true, true, "dr.title"},
 *          {"facility", "string", true, true, "f.name"},
 *     }
 * )
 */
class DiningRoom
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_admin_dining_room_grid", "api_admin_dining_room_list", "api_admin_dining_room_get"})
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_dining_room_add", "api_admin_dining_room_edit"})
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_dining_room_add", "api_admin_dining_room_edit"}
     * )
     * @ORM\Column(name="title", type="string", unique=true, length=50)
     * @Groups({"api_admin_dining_room_grid", "api_admin_dining_room_list", "api_admin_dining_room_get"})
     */
    private $title;

    /**
     * @var Facility
     * @Assert\NotNull(message = "Please select a Facility", groups={"api_admin_dining_room_add", "api_admin_dining_room_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Facility")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_dining_room_grid", "api_admin_dining_room_list", "api_admin_dining_room_get"})
     */
    private $facility;

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

    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    public function setFacility(?Facility $facility): self
    {
        $this->facility = $facility;

        return $this;
    }
}
