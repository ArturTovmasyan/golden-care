<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class FacilityRoomType
 *
 * @ORM\Entity(repositoryClass="App\Repository\FacilityRoomTypeRepository")
 * @UniqueEntity(
 *     fields={"facility", "title"},
 *     errorPath="number",
 *     message="The title is already in use for this Facility.",
 *     groups={
 *         "api_admin_facility_room_type_add",
 *         "api_admin_facility_room_type_edit"
 * })
 * @ORM\Table(name="tbl_facility_room_type")
 * @Grid(
 *     api_admin_facility_room_type_grid={
 *          {
 *              "id"         = "facility",
 *              "type"       = "string",
 *              "field"      = "f.name"
 *          },
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "frt.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "frt.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "private",
 *              "type"       = "boolean",
 *              "field"      = "frt.private"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(frt.description, 1, 100)), CASE WHEN LENGTH(frt.description) > 100 THEN '…' ELSE '' END)"
 *          }
 *     }
 * )
 */
class FacilityRoomType
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_facility_room_type_list",
     *     "api_admin_facility_room_type_get",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get",
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get",
     *     "api_admin_facility_room_base_rate_get",
     *     "api_admin_facility_room_base_rate_list",
     *     "api_admin_resident_admission_get_active"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_room_type_add",
     *     "api_admin_facility_room_type_edit"
     * })
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_facility_room_type_add",
     *          "api_admin_facility_room_type_edit"
     * })
     * @ORM\Column(name="title", type="string", length=50)
     * @Groups({
     *     "api_admin_facility_room_type_list",
     *     "api_admin_facility_room_type_get",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get",
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get",
     *     "api_admin_facility_room_base_rate_get",
     *     "api_admin_facility_room_base_rate_list",
     *     "api_admin_resident_admission_get_active"
     * })
     */
    private $title;

    /**
     * @var Facility
     * @Assert\NotNull(message = "Please select a Facility", groups={
     *     "api_admin_facility_room_type_add",
     *     "api_admin_facility_room_type_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Facility", inversedBy="diningRooms", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_facility_room_type_list",
     *     "api_admin_facility_room_type_get"
     * })
     */
    private $facility;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=1000, nullable=true)
     * @Assert\Length(
     *      max = 1000,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_facility_room_type_add",
     *          "api_admin_facility_room_type_edit"
     * })
     * @Groups({
     *     "api_admin_facility_room_type_list",
     *     "api_admin_facility_room_type_get"
     * })
     */
    private $description;

    /**
     * @var bool
     * @ORM\Column(name="private", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_admin_facility_room_type_list",
     *     "api_admin_facility_room_type_get",
     *     "api_admin_resident_admission_get_active"
     * })
     */
    private $private;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityRoomBaseRate", mappedBy="roomType", cascade={"persist"})
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $baseRates;


    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityRoom", mappedBy="type", cascade={"remove", "persist"})
     */
    private $rooms;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityRoomTypes", mappedBy="type", cascade={"remove", "persist"})
     */
    private $types;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("levels")
     * @Serializer\Groups({
     *     "api_admin_resident_admission_get_active"
     * })
     * @return Collection|FacilityRoomBaseRate[]|null
     */
    public function getRates()
    {
        $now = new \DateTime('now');

        $criteria = Criteria::create()
            ->where(Criteria::expr()->lt('date', $now))
            ->orderBy(array('date' => Criteria::DESC))
            ->setMaxResults(1)
        ;

        /** @var FacilityRoomBaseRate[] $data */
        $data = $this->baseRates->matching($criteria);

        if(\count($data) > 0) {
            $data = $data[0]->getLevels();
        }

        return $data;
    }

    /**
     * @var int
     * @Groups({
     *     "api_admin_facility_room_type_list"
     * })
     */
    private $count;

    /**
     * @return int|null
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * @param int|null $count
     */
    public function setCount(?int $count): void
    {
        $this->count = $count;
    }

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
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = preg_replace('/\s\s+/', ' ', $title);
    }

    /**
     * @return Facility|null
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * @param Facility|null $facility
     */
    public function setFacility(?Facility $facility): void
    {
        $this->facility = $facility;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param bool $private
     */
    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }

    /**
     * @return mixed
     */
    public function getBaseRates()
    {
        return $this->baseRates;
    }

    /**
     * @param mixed $baseRates
     */
    public function setBaseRates($baseRates): void
    {
        $this->baseRates = $baseRates;
    }

    /**
     * @return ArrayCollection
     */
    public function getRooms(): ArrayCollection
    {
        return $this->rooms;
    }

    /**
     * @param ArrayCollection $rooms
     */
    public function setRooms(ArrayCollection $rooms): void
    {
        $this->rooms = $rooms;
    }

    /**
     * @return mixed
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param mixed $types
     */
    public function setTypes($types): void
    {
        $this->types = $types;
    }
}
