<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use App\Model\RoomType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ApartmentRoom
 *
 * @ORM\Entity(repositoryClass="App\Repository\ApartmentRoomRepository")
 * @ORM\Table(name="tbl_apartment_room")
 * @Grid(
 *     api_admin_apartment_room_grid={
 *          {"id", "number", true, true, "ar.id"},
 *          {"apartment", "string", true, true, "a.name"},
 *          {"number", "string", true, true, "ar.number"},
 *          {"type", "number", true, true, "ar.type"},
 *          {"floor", "number", true, true, "ar.floor"},
 *          {"disabled", "boolean", true, true, "ar.disabled"},
 *          {"shared", "boolean", true, true, "ar.shared"},
 *          {"notes", "string", true, true, "ar.notes"},
 *     }
 * )
 */
class ApartmentRoom
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_apartment_room_grid",
     *     "api_admin_apartment_room_list",
     *     "api_admin_apartment_room_get"
     * })
     */
    private $id;

    /**
     * @var Apartment
     * @Assert\NotNull(message = "Please select a Apartment", groups={"api_admin_apartment_room_add", "api_admin_apartment_room_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Apartment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_apartment", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_apartment_room_grid", "api_admin_apartment_room_list", "api_admin_apartment_room_get"})
     */
    private $apartment;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_apartment_room_add", "api_admin_apartment_room_edit"})
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Number cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_apartment_room_add", "api_admin_apartment_room_edit"}
     * )
     * @ORM\Column(name="number", type="string", length=10)
     * @Groups({"api_admin_apartment_room_grid", "api_admin_apartment_room_list", "api_admin_apartment_room_get"})
     */
    private $number;

    /**
     * @var int
     * @Assert\NotBlank(groups={"api_admin_apartment_room_add", "api_admin_apartment_room_edit"})
     * @Assert\Regex(
     *      pattern="/(^[1-2]$)/",
     *      message="The value should be 1 or 2",
     *      groups={"api_admin_apartment_room_add", "api_admin_apartment_room_edit"}
     * )
     * @Assert\Length(
     *      max = 1,
     *      maxMessage = "Type cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_apartment_room_add", "api_admin_apartment_room_edit"}
     * )
     * @ORM\Column(name="type", type="integer", length=1)
     * @Groups({"api_admin_apartment_room_grid", "api_admin_apartment_room_list", "api_admin_apartment_room_get"})
     */
    private $type = RoomType::PRIVATE;

    /**
     * @var int
     * @Assert\NotBlank(groups={"api_admin_apartment_room_add", "api_admin_apartment_room_edit"})
     * @Assert\Regex(
     *      pattern="/(^[1-9][0-9]*$)/",
     *      message="The value should be numeric",
     *      groups={"api_admin_apartment_add", "api_admin_apartment_edit"}
     * )
     * @Assert\Length(
     *      max = 2,
     *      maxMessage = "Floor cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_apartment_room_add", "api_admin_apartment_room_edit"}
     * )
     * @ORM\Column(name="floor", type="integer", length=2)
     * @Groups({"api_admin_apartment_room_grid", "api_admin_apartment_room_list", "api_admin_apartment_room_get"})
     */
    private $floor = 1;

    /**
     * @var bool
     * @ORM\Column(name="disabled", type="boolean", options={"default" = 0})
     * @Groups({"api_admin_apartment_room_grid", "api_admin_apartment_room_list", "api_admin_apartment_room_get"})
     */
    protected $disabled;

    /**
     * @var bool
     * @ORM\Column(name="shared", type="boolean", options={"default" = 0})
     * @Groups({"api_admin_apartment_room_grid", "api_admin_apartment_room_list", "api_admin_apartment_room_get"})
     */
    protected $shared;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=1000, nullable=true)
     * @Assert\Length(
     *      max = 1000,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_apartment_room_add", "api_admin_apartment_room_edit"}
     * )
     * @Groups({"api_admin_apartment_room_grid", "api_admin_apartment_room_list", "api_admin_apartment_room_get"})
     */
    private $notes;

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
     * @return Apartment|null
     */
    public function getApartment(): ?Apartment
    {
        return $this->apartment;
    }

    /**
     * @param Apartment|null $apartment
     * @return ApartmentRoom
     */
    public function setApartment(?Apartment $apartment): self
    {
        $this->apartment = $apartment;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @param null|string $number
     */
    public function setNumber(?string $number): void
    {
        $this->number = preg_replace('/\s\s+/', ' ', $number);
    }

    public function getType(): ?int
{
    return $this->type;
}

    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getFloor(): ?int
    {
        return $this->floor;
    }

    public function setFloor($floor): self
    {
        $this->floor = $floor;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     */
    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }

    /**
     * @return bool
     */
    public function isShared(): bool
    {
        return $this->shared;
    }

    /**
     * @param bool $shared
     */
    public function setShared(bool $shared): void
    {
        $this->shared = $shared;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }
}
