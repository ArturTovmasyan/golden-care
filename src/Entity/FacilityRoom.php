<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class FacilityRoom
 *
 * @ORM\Entity(repositoryClass="App\Repository\FacilityRoomRepository")
 * @UniqueEntity(
 *     fields={"facility", "number"},
 *     errorPath="number",
 *     message="This number is already in use on that facility",
 *     groups={"api_admin_facility_room_add", "api_admin_facility_room_edit"}
 * )
 * @ORM\Table(name="tbl_facility_room")
 * @Grid(
 *     api_admin_facility_room_grid={
 *          {"id", "number", true, true, "fr.id"},
 *          {"number", "string", true, true, "fr.number"},
 *          {"floor", "number", true, true, "fr.floor"},
 *          {"notes", "string", true, true, "fr.notes"},
 *          {"facility", "string", true, true, "f.name"},
 *     }
 * )
 */
class FacilityRoom
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_facility_room_grid",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get",
     *     "api_admin_resident_get"
     * })
     */
    private $id;

    /**
     * @var Facility
     * @Assert\NotNull(message = "Please select a Facility", groups={"api_admin_facility_room_add", "api_admin_facility_room_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Facility", inversedBy="rooms", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_facility_room_grid", "api_admin_facility_room_list", "api_admin_facility_room_get"})
     */
    private $facility;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_facility_room_add", "api_admin_facility_room_edit"})
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Number cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_room_add", "api_admin_facility_room_edit"}
     * )
     * @ORM\Column(name="number", type="string", length=10)
     * @Groups({
     *     "api_admin_facility_room_grid",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get",
     *     "api_admin_resident_get"
     * })
     */
    private $number;

    /**
     * @var int
     * @Assert\NotBlank(groups={"api_admin_facility_room_add", "api_admin_facility_room_edit"})
     * @Assert\Regex(
     *      pattern="/(^[1-9][0-9]*$)/",
     *      message="The value should be numeric",
     *      groups={"api_admin_facility_add", "api_admin_facility_edit"}
     * )
     * @Assert\Length(
     *      max = 2,
     *      maxMessage = "Floor cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_room_add", "api_admin_facility_room_edit"}
     * )
     * @ORM\Column(name="floor", type="integer", length=2)
     * @Groups({"api_admin_facility_room_grid", "api_admin_facility_room_list", "api_admin_facility_room_get"})
     */
    private $floor = 1;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=1000, nullable=true)
     * @Assert\Length(
     *      max = 1000,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_facility_room_add", "api_admin_facility_room_edit"}
     * )
     * @Groups({"api_admin_facility_room_grid", "api_admin_facility_room_list", "api_admin_facility_room_get"})
     */
    private $notes;

    /**
     * @var ArrayCollection
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "You must specify at least one Bed",
     *      groups={"api_admin_facility_room_add", "api_admin_facility_room_edit"}
     * )
     * @Assert\Valid(groups={"api_admin_facility_room_add", "api_admin_facility_room_edit"})
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityBed", mappedBy="room", cascade={"remove", "persist"})
     * @Groups({"api_admin_facility_room_grid", "api_admin_facility_room_list", "api_admin_facility_room_get"})
     */
    private $beds;

    public function __construct()
    {
        $this->beds = new ArrayCollection();
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
     * @return Facility|null
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * @param Facility|null $facility
     * @return FacilityRoom
     */
    public function setFacility(?Facility $facility): self
    {
        $this->facility = $facility;

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

    public function getFloor(): ?int
    {
        return $this->floor;
    }

    public function setFloor($floor): self
    {
        $this->floor = $floor;

        return $this;
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

    /**
     * @return ArrayCollection
     */
    public function getBeds()
    {
        return $this->beds;
    }

    /**
     * @param ArrayCollection $beds
     */
    public function setBeds(ArrayCollection $beds): void
    {
        $this->beds = $beds;
    }

    /**
     * @param FacilityBed $bed
     */
    public function addBed($bed): void
    {
        $bed->setRoom($this);
        $this->beds->add($bed);
    }

    /**
     * @param FacilityBed $bed
     */
    public function removeBed($bed): void
    {
        $this->beds->removeElement($bed);
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback(groups={"api_admin_facility_room_add", "api_admin_facility_room_edit"})
     */
    public function areBedsNumberValid(ExecutionContextInterface $context): void
    {
        $beds = $this->getBeds();
        if ($beds !== null) {
            $numbers = array_map(function($item){return strtolower($item->getNumber());} , $beds->toArray());

            $counts = array_count_values($numbers);

            /**
             * @var integer $idx
             * @var FacilityBed $bed
             */
            foreach ($beds as $idx => $bed) {
                $number = strtolower($bed->getNumber());
                if (!empty($counts[$number]) && $counts[$number] > 1) {
                    $context->buildViolation('The number "'.$bed->getNumber().'" is already in use.')
                        ->atPath("beds.$idx.number")
                        ->addViolation();
                }
            }
        }
    }
}
