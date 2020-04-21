<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * Class FacilityRoomTypes
 *
 * @ORM\Entity()
 * @ORM\Table(name="tbl_facility_room_facility_room_types")
 */
class FacilityRoomTypes
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var FacilityRoom
     * @ORM\ManyToOne(targetEntity="App\Entity\FacilityRoom", inversedBy="types")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_room", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $room;

    /**
     * @var FacilityRoomType
     * @ORM\ManyToOne(targetEntity="App\Entity\FacilityRoomType", inversedBy="types")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_type", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get"
     * })
     */
    private $type;

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
     * @return FacilityRoom|null
     */
    public function getRoom(): ?FacilityRoom
    {
        return $this->room;
    }

    /**
     * @param FacilityRoom|null $room
     */
    public function setRoom(?FacilityRoom $room): void
    {
        $this->room = $room;
    }

    /**
     * @return FacilityRoomType|null
     */
    public function getType(): ?FacilityRoomType
    {
        return $this->type;
    }

    /**
     * @param FacilityRoomType|null $type
     */
    public function setType(?FacilityRoomType $type): void
    {
        $this->type = $type;
    }
}
