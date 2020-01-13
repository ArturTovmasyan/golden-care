<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentAdmission
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentAdmissionRepository")
 * @ORM\Table(name="tbl_resident_admission")
 * @Grid(
 *     api_admin_resident_admission_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ra.id"
 *          },
 *          {
 *              "id"         = "admission_type",
 *              "type"       = "enum",
 *              "field"      = "ra.admissionType",
 *              "values"     = "\App\Model\AdmissionType::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "ra.date"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(ra.notes, 1, 100)), CASE WHEN LENGTH(ra.notes) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "info",
 *              "sortable"   = false,
 *              "type"       = "json",
 *              "field"      = "info"
 *          }
 *     }
 * )
 */
class ResidentAdmission
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentAdmissions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit",
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit",
     *     "api_admin_region_add",
     *     "api_admin_region_edit",
     *     "api_admin_discharge_add",
     *     "api_admin_discharge_edit"
     *
     * })
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get"
     * })
     */
    private $resident;

    /**
     * @var int
     * @ORM\Column(name="group_type", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\GroupType","getTypeValues"},
     *     groups={
     *          "api_admin_facility_add",
     *          "api_admin_apartment_add",
     *          "api_admin_region_add",
     *     }
     * )
     * @Groups({
     *      "api_admin_resident_admission_list",
     *      "api_admin_resident_admission_get",
     *      "api_admin_resident_admission_get_active",
     *      "api_admin_resident_get_last_admission"
     * })
     */
    private $groupType;

    /**
     * @var int
     * @ORM\Column(name="admission_type", type="integer", length=1)
     * @Assert\Choice(
     *     callback={"App\Model\AdmissionType","getTypeValues"},
     *     groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit",
     *          "api_admin_apartment_add",
     *          "api_admin_apartment_edit",
     *          "api_admin_region_add",
     *          "api_admin_region_edit",
     *          "api_admin_discharge_add",
     *          "api_admin_discharge_edit"
     *     }
     * )
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $admissionType;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit",
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit",
     *     "api_admin_region_add",
     *     "api_admin_region_edit",
     *     "api_admin_discharge_add",
     *     "api_admin_discharge_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit",
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit",
     *     "api_admin_region_add",
     *     "api_admin_region_edit",
     *     "api_admin_discharge_add",
     *     "api_admin_discharge_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $date;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit",
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit",
     *     "api_admin_region_add",
     *     "api_admin_region_edit",
     *     "api_admin_discharge_add",
     *     "api_admin_discharge_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit",
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit",
     *     "api_admin_region_add",
     *     "api_admin_region_edit",
     *     "api_admin_discharge_add",
     *     "api_admin_discharge_edit"
     * })
     * @ORM\Column(name="start", type="datetime")
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $start;

    /**
     * @var \DateTime
     * @Assert\DateTime(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit",
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit",
     *     "api_admin_region_add",
     *     "api_admin_region_edit",
     *     "api_admin_discharge_add",
     *     "api_admin_discharge_edit"
     * })
     * @ORM\Column(name="end", type="datetime", nullable=true)
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $end;

    /**
     * @var FacilityBed
     * @ORM\ManyToOne(targetEntity="App\Entity\FacilityBed", inversedBy="residentAdmissions")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_facility_bed", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Facility Bed", groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $facilityBed;

    /**
     * @var ApartmentBed
     * @ORM\ManyToOne(targetEntity="App\Entity\ApartmentBed", inversedBy="residentAdmissions")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_apartment_bed", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select an Apartment Bed", groups={
     *     "api_admin_apartment_add",
     *     "api_admin_apartment_edit"
     * })
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $apartmentBed;

    /**
     * @var Region
     * @ORM\ManyToOne(targetEntity="App\Entity\Region", inversedBy="residentAdmissions")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_region", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Region", groups={
     *     "api_admin_region_add",
     *     "api_admin_region_edit"
     * })
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $region;

    /**
     * @var CityStateZip
     * @ORM\ManyToOne(targetEntity="App\Entity\CityStateZip", inversedBy="residentAdmissions")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a City, State and Zip", groups={
     *     "api_admin_region_add",
     *     "api_admin_region_edit",
     *     "api_admin_resident_region_edit"
     * })
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $csz;

    /**
     * @var string
     * @ORM\Column(name="address", type="string", length=256, nullable=true)
     * @Assert\NotBlank(groups={
     *     "api_admin_region_add",
     *     "api_admin_region_edit",
     *     "api_admin_resident_region_edit"
     * })
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Address cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_region_add",
     *          "api_admin_region_edit",
     *          "api_admin_resident_region_edit"
     * })
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $address;

    /**
     * @var DiningRoom
     * @ORM\ManyToOne(targetEntity="App\Entity\DiningRoom", inversedBy="residentAdmissions")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_dining_room", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Dining Room", groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit"
     * })
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $diningRoom;

    /**
     * @var bool
     * @ORM\Column(name="dnr", type="boolean", nullable=true)
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $dnr;

    /**
     * @var bool
     * @ORM\Column(name="polst", type="boolean", nullable=true)
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $polst;

    /**
     * @var bool
     * @ORM\Column(name="ambulatory", type="boolean", nullable=true)
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $ambulatory;

    /**
     * @var int
     * @ORM\Column(name="care_group", type="smallint", nullable=true)
     * @Assert\Regex(
     *     pattern = "/^[1-9][0-9]*$/",
     *     message="Please provide a valid Care Group.",
     *     groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit",
     *          "api_admin_region_add",
     *          "api_admin_region_edit",
     *          "api_admin_resident_facility_edit",
     *          "api_admin_resident_region_edit",
     *          "api_admin_resident_region_edit_mobile"
     * }
     * )
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit",
     *     "api_admin_region_add",
     *     "api_admin_region_edit",
     *     "api_admin_resident_facility_edit",
     *     "api_admin_resident_region_edit",
     *     "api_admin_resident_region_edit_mobile"
     * })
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $careGroup;

    /**
     * @var CareLevel
     * @ORM\ManyToOne(targetEntity="App\Entity\CareLevel", inversedBy="residentAdmissions")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_care_level", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Care Level", groups={
     *     "api_admin_facility_add",
     *     "api_admin_facility_edit",
     *     "api_admin_region_add",
     *     "api_admin_region_edit",
     *     "api_admin_resident_facility_edit",
     *     "api_admin_resident_region_edit",
     *     "api_admin_resident_region_edit_mobile"
     * })
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $careLevel;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_facility_add",
     *          "api_admin_facility_edit",
     *          "api_admin_apartment_add",
     *          "api_admin_apartment_edit",
     *          "api_admin_region_add",
     *          "api_admin_region_edit",
     *          "api_admin_discharge_add",
     *          "api_admin_discharge_edit"
     * })
     * @Groups({
     *     "api_admin_resident_admission_grid",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_resident_get_last_admission"
     * })
     */
    private $notes;

    /**
     * @return int
     */
    public function getId() : ?int
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
     * @return Resident|null
     */
    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident|null $resident
     */
    public function setResident(?Resident $resident): void
    {
        $this->resident = $resident;
    }

    /**
     * @return int|null
     */
    public function getGroupType(): ?int
    {
        return $this->groupType;
    }

    /**
     * @param $groupType
     */
    public function setGroupType($groupType): void
    {
        $this->groupType = $groupType;
    }

    public function getAdmissionType(): ?int
    {
        return $this->admissionType;
    }

    public function setAdmissionType($admissionType): void
    {
        $this->admissionType = $admissionType;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }

    /**
     * @return \DateTime|null
     */
    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    /**
     * @param \DateTime $start
     */
    public function setStart($start): void
    {
        $this->start = $start;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime $end
     */
    public function setEnd($end): void
    {
        $this->end = $end;
    }

    /**
     * @return FacilityBed|null
     */
    public function getFacilityBed(): ?FacilityBed
    {
        return $this->facilityBed;
    }

    /**
     * @param FacilityBed|null $facilityBed
     */
    public function setFacilityBed(?FacilityBed $facilityBed): void
    {
        $this->facilityBed = $facilityBed;
    }

    /**
     * @return ApartmentBed|null
     */
    public function getApartmentBed(): ?ApartmentBed
    {
        return $this->apartmentBed;
    }

    /**
     * @param ApartmentBed|null $apartmentBed
     */
    public function setApartmentBed(?ApartmentBed $apartmentBed): void
    {
        $this->apartmentBed = $apartmentBed;
    }

    /**
     * @return Region|null
     */
    public function getRegion(): ?Region
    {
        return $this->region;
    }

    /**
     * @param Region|null $region
     */
    public function setRegion(?Region $region): void
    {
        $this->region = $region;
    }

    /**
     * @return CityStateZip|null
     */
    public function getCsz(): ?CityStateZip
    {
        return $this->csz;
    }

    /**
     * @param CityStateZip|null $csz
     */
    public function setCsz(?CityStateZip $csz): void
    {
        $this->csz = $csz;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return DiningRoom|null
     */
    public function getDiningRoom(): ?DiningRoom
    {
        return $this->diningRoom;
    }

    /**
     * @param DiningRoom|null $diningRoom
     */
    public function setDiningRoom(?DiningRoom $diningRoom): void
    {
        $this->diningRoom = $diningRoom;
    }

    /**
     * @return bool|null
     */
    public function isDnr(): ?bool
    {
        return $this->dnr;
    }

    /**
     * @param bool|null $dnr
     */
    public function setDnr(?bool $dnr): void
    {
        $this->dnr = $dnr;
    }

    /**
     * @return bool|null
     */
    public function isPolst(): ?bool
    {
        return $this->polst;
    }

    /**
     * @param bool|null $polst
     */
    public function setPolst(?bool $polst):void
    {
        $this->polst = $polst;
    }

    /**
     * @return bool|null
     */
    public function isAmbulatory(): ?bool
    {
        return $this->ambulatory;
    }

    /**
     * @param bool|null $ambulatory
     */
    public function setAmbulatory(?bool $ambulatory): void
    {
        $this->ambulatory = $ambulatory;
    }

    /**
     * @return int|null
     */
    public function getCareGroup(): ?int
    {
        return $this->careGroup;
    }

    /**
     * @param int|null $careGroup
     */
    public function setCareGroup(?int $careGroup): void
    {
        $this->careGroup = $careGroup;
    }

    /**
     * @return CareLevel|null
     */
    public function getCareLevel(): ?CareLevel
    {
        return $this->careLevel;
    }

    /**
     * @param CareLevel|null $careLevel
     */
    public function setCareLevel(?CareLevel $careLevel): void
    {
        $this->careLevel = $careLevel;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }
}
