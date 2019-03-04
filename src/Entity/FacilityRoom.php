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
 *     groups={
 *         "api_admin_facility_room_add",
 *         "api_admin_facility_room_edit"
 * })
 * @ORM\Table(name="tbl_facility_room")
 * @Grid(
 *     api_admin_facility_room_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "fr.id"
 *          },
 *          {
 *              "id"         = "number",
 *              "type"       = "string",
 *              "field"      = "fr.number",
 *              "sort_type"  = "natural",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "floor",
 *              "type"       = "number",
 *              "field"      = "fr.floor"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "fr.notes"
 *          },
 *          {
 *              "id"         = "facility",
 *              "type"       = "string",
 *              "field"      = "f.name"
 *          }
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
     *     "api_admin_resident_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $id;

    /**
     * @var Facility
     * @Assert\NotNull(message = "Please select a Facility", groups={
     *     "api_admin_facility_room_add",
     *     "api_admin_facility_room_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Facility", inversedBy="rooms", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_facility_room_grid",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get",
     *     "api_admin_contract_get_active",
     *     "api_admin_contract_get"
     * })
     */
    private $facility;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_room_add",
     *     "api_admin_facility_room_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Number cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_facility_room_add",
     *          "api_admin_facility_room_edit"
     * })
     * @ORM\Column(name="number", type="string", length=10)
     * @Groups({
     *     "api_admin_facility_room_grid",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get",
     *     "api_admin_resident_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $number;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_room_add",
     *     "api_admin_facility_room_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^[1-9][0-9]?$)/",
     *      message="The value should be numeric and more than zero and no longer than 2 characters.",
     *      groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit"
     * })
     * @ORM\Column(name="floor", type="integer", length=2)
     * @Groups({
     *     "api_admin_facility_room_grid",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $floor = 1;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=1000, nullable=true)
     * @Assert\Length(
     *      max = 1000,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_facility_room_add",
     *          "api_admin_facility_room_edit"
     * })
     * @Groups({
     *     "api_admin_facility_room_grid",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get"
     * })
     */
    private $notes;

    /**
     * @var ArrayCollection
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "You must specify at least one bed.",
     *      groups={
     *          "api_admin_facility_room_add",
     *          "api_admin_facility_room_edit"
     * })
     * @Assert\Valid(groups={
     *     "api_admin_facility_room_add",
     *     "api_admin_facility_room_edit"
     * })
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityBed", mappedBy="room", cascade={"remove", "persist"})
     * @Groups({
     *     "api_admin_facility_room_grid",
     *     "api_admin_facility_room_list",
     *     "api_admin_facility_room_get"
     * })
     */
    private $beds;

    public function __construct()
    {
        $this->beds = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
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
    public function setFacility(?Facility $facility): void
    {
        $this->facility = $facility;
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

    public function setFloor($floor): void
    {
        $this->floor = $floor;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
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
     * @Assert\Callback(groups={
     *     "api_admin_facility_room_add",
     *     "api_admin_facility_room_edit"
     * })
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
