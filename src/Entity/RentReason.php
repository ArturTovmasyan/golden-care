<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class RentReason
 *
 * @ORM\Entity(repositoryClass="App\Repository\RentReasonRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_rent_reason_add",
 *          "api_admin_rent_reason_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_rent_reason")
 * @Grid(
 *     api_admin_rent_reason_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rrn.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "rrn.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(rrn.notes, 1, 100)), CASE WHEN LENGTH(rrn.notes) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class RentReason
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_rent_reason_list",
     *     "api_admin_rent_reason_get",
     *     "api_admin_resident_rent_increase_list",
     *     "api_admin_resident_rent_increase_get",
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_admin_rent_reason_add",
     *          "api_admin_rent_reason_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_rent_reason_add",
     *          "api_admin_rent_reason_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=200)
     * @Groups({
     *     "api_admin_rent_reason_list",
     *     "api_admin_rent_reason_get",
     *     "api_admin_resident_rent_increase_list",
     *     "api_admin_resident_rent_increase_get",
     *     "api_admin_resident_rent_list",
     *     "api_admin_resident_rent_get"
     * })
     */
    private $title;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=255, nullable=true)
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_rent_reason_add",
     *          "api_admin_rent_reason_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_rent_reason_list",
     *     "api_admin_rent_reason_get"
     * })
     */
    private $notes;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_rent_reason_add",
     *     "api_admin_rent_reason_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="rentReasons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_rent_reason_list",
     *     "api_admin_rent_reason_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentRentIncrease", mappedBy="reason", cascade={"remove", "persist"})
     */
    private $residentRentIncreases;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentRent", mappedBy="reason", cascade={"remove", "persist"})
     */
    private $residentRents;

    public function getId(): ?int
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


    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @return Space|null
     */
    public function getSpace(): ?Space
    {
        return $this->space;
    }

    /**
     * @param Space|null $space
     */
    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentRentIncreases(): ArrayCollection
    {
        return $this->residentRentIncreases;
    }

    /**
     * @param ArrayCollection $residentRentIncreases
     */
    public function setResidentRentIncreases(ArrayCollection $residentRentIncreases): void
    {
        $this->residentRentIncreases = $residentRentIncreases;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentRents(): ArrayCollection
    {
        return $this->residentRents;
    }

    /**
     * @param ArrayCollection $residentRents
     */
    public function setResidentRents(ArrayCollection $residentRents): void
    {
        $this->residentRents = $residentRents;
    }
}
