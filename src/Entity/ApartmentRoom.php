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
 * Class ApartmentRoom
 *
 * @ORM\Entity(repositoryClass="App\Repository\ApartmentRoomRepository")
 * @UniqueEntity(
 *     fields={"apartment", "number"},
 *     errorPath="number",
 *     message="This number is already in use on that apartment.",
 *     groups={
 *         "api_admin_apartment_room_add",
 *         "api_admin_apartment_room_edit"
 * })
 * @ORM\Table(name="tbl_apartment_room")
 * @Grid(
 *     api_admin_apartment_room_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ar.id"
 *          },
 *          {
 *              "id"         = "number",
 *              "type"       = "string",
 *              "field"      = "ar.number",
 *              "sort_type"  = "natural",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "floor",
 *              "type"       = "number",
 *              "field"      = "ar.floor"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "ar.notes"
 *          },
 *          {
 *              "id"         = "apartment",
 *              "type"       = "string",
 *              "field"      = "a.name"
 *          }
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
     *     "api_admin_apartment_room_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active"
     * })
     */
    private $id;

    /**
     * @var Apartment
     * @Assert\NotNull(message = "Please select a Apartment", groups={
     *     "api_admin_apartment_room_add",
     *     "api_admin_apartment_room_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Apartment", inversedBy="rooms", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_apartment", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_apartment_room_grid",
     *     "api_admin_apartment_room_list",
     *     "api_admin_apartment_room_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active",
     *     "api_admin_contract_get"
     * })
     */
    private $apartment;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_apartment_room_add",
     *     "api_admin_apartment_room_edit"
     * })
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Number cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_apartment_room_add",
     *          "api_admin_apartment_room_edit"
     * })
     * @ORM\Column(name="number", type="string", length=10)
     * @Groups({
     *     "api_admin_apartment_room_grid",
     *     "api_admin_apartment_room_list",
     *     "api_admin_apartment_room_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_get_active"
     * })
     */
    private $number;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_apartment_room_add",
     *     "api_admin_apartment_room_edit"
     * })
     * @Assert\Regex(
     *      pattern="/(^[1-9][0-9]?$)/",
     *      message="The value should be numeric and more than zero and no longer than 2 characters.",
     *      groups={
     *          "api_admin_apartment_add",
     *          "api_admin_apartment_edit"
     * })
     * @ORM\Column(name="floor", type="integer", length=2)
     * @Groups({
     *     "api_admin_apartment_room_grid",
     *     "api_admin_apartment_room_list",
     *     "api_admin_apartment_room_get",
     *     "api_admin_resident_admission_get_active",
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
     *          "api_admin_apartment_room_add",
     *          "api_admin_apartment_room_edit"
     * })
     * @Groups({
     *     "api_admin_apartment_room_grid",
     *     "api_admin_apartment_room_list",
     *     "api_admin_apartment_room_get"
     * })
     */
    private $notes;

    /**
     * @var ArrayCollection
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "You must specify at least one bed.",
     *      groups={
     *          "api_admin_apartment_room_add",
     *          "api_admin_apartment_room_edit"
     * })
     * @Assert\Valid(groups={
     *     "api_admin_apartment_room_add",
     *     "api_admin_apartment_room_edit"
     * })
     * @ORM\OneToMany(targetEntity="App\Entity\ApartmentBed", mappedBy="room", cascade={"remove", "persist"})
     * @Groups({
     *     "api_admin_apartment_room_grid",
     *     "api_admin_apartment_room_list",
     *     "api_admin_apartment_room_get"
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
     * @return Apartment|null
     */
    public function getApartment(): ?Apartment
    {
        return $this->apartment;
    }

    /**
     * @param Apartment|null $apartment
     */
    public function setApartment(?Apartment $apartment): void
    {
        $this->apartment = $apartment;
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
     * @param ApartmentBed $bed
     */
    public function addBed($bed): void
    {
        $bed->setRoom($this);
        $this->beds->add($bed);
    }

    /**
     * @param ApartmentBed $bed
     */
    public function removeBed($bed): void
    {
        $this->beds->removeElement($bed);
    }

    /**
     * @param ExecutionContextInterface $context
     * @Assert\Callback(groups={
     *     "api_admin_apartment_room_add",
     *     "api_admin_apartment_room_edit"
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
             * @var ApartmentBed $bed
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
