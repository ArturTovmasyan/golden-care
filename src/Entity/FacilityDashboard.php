<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class FacilityDashboard
 *
 * @ORM\Entity(repositoryClass="App\Repository\FacilityDashboardRepository")
 * @ORM\Table(name="tbl_facility_dashboard")
 * @Grid(
 *     api_admin_facility_dashboard_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "fd.id"
 *          },
 *          {
 *              "id"         = "facility",
 *              "type"       = "string",
 *              "field"      = "f.name"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "fd.date"
 *          },
 *          {
 *              "id"         = "total_capacity",
 *              "type"       = "number",
 *              "field"      = "fd.totalCapacity"
 *          },
 *          {
 *              "id"         = "break_even",
 *              "type"       = "number",
 *              "field"      = "fd.breakEven"
 *          },
 *          {
 *              "id"         = "capacity_yellow",
 *              "type"       = "number",
 *              "field"      = "fd.capacityYellow"
 *          },
 *          {
 *              "id"         = "occupancy",
 *              "type"       = "number",
 *              "field"      = "fd.occupancy"
 *          },
 *          {
 *              "id"         = "move_ins_respite",
 *              "type"       = "number",
 *              "field"      = "fd.moveInsRespite"
 *          },
 *          {
 *              "id"         = "move_outs_respite",
 *              "type"       = "number",
 *              "field"      = "fd.moveOutsRespite"
 *          },
 *          {
 *              "id"         = "move_ins_long_term",
 *              "type"       = "number",
 *              "field"      = "fd.moveInsLongTerm"
 *          },
 *          {
 *              "id"         = "move_outs_long_term",
 *              "type"       = "number",
 *              "field"      = "fd.moveOutsLongTerm"
 *          }
 *     }
 * )
 */
class FacilityDashboard
{
    use TimeAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $id;

    /**
     * @var Facility
     * @Assert\NotNull(message = "Please select a Facility", groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Facility", inversedBy="dashboards", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $facility;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *      "api_admin_facility_dashboard_add",
     *      "api_admin_facility_dashboard_edit"
     * })
     * @Assert\DateTime(groups={
     *      "api_admin_facility_dashboard_add",
     *      "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $date;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @Assert\Regex(
     *      pattern="/^[1-9][0-9]*$/",
     *      message="The value should be numeric.",
     *      groups={
     *          "api_admin_facility_dashboard_add",
     *          "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="total_capacity", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $totalCapacity;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @Assert\Regex(
     *      pattern="/^[1-9][0-9]*$/",
     *      message="The value should be numeric.",
     *      groups={
     *          "api_admin_facility_dashboard_add",
     *          "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="break_even", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $breakEven;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @Assert\Regex(
     *      pattern="/^[1-9][0-9]*$/",
     *      message="The value should be numeric.",
     *      groups={
     *          "api_admin_facility_dashboard_add",
     *          "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="capacity_yellow", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $capacityYellow;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="occupancy", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $occupancy = 0;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="move_ins_respite", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $moveInsRespite = 0;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="move_outs_respite", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $moveOutsRespite = 0;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="move_ins_long_term", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $moveInsLongTerm = 0;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="move_outs_long_term", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $moveOutsLongTerm = 0;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="hot_leads", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $hotLeads = 0;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="notice_to_vacate", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $noticeToVacate = 0;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="tours_per_month", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $toursPerMonth = 0;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="total_inquiries", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $totalInquiries = 0;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="qualified_inquiries", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $qualifiedInquiries = 0;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="outreach_per_month", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $outreachPerMonth = 0;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="events_per_month", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $eventsPerMonth = 0;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_dashboard_add",
     *     "api_admin_facility_dashboard_edit"
     * })
     * @ORM\Column(name="average_room_rent", type="integer")
     * @Groups({
     *     "api_admin_facility_dashboard_list",
     *     "api_admin_facility_dashboard_get"
     * })
     */
    private $averageRoomRent = 0;

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
     */
    public function setFacility(?Facility $facility): void
    {
        $this->facility = $facility;
    }

    /**
     * @return \DateTime|null
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime|null $date
     */
    public function setDate(?\DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return int|null
     */
    public function getTotalCapacity(): ?int
    {
        return $this->totalCapacity;
    }

    /**
     * @param int|null $totalCapacity
     */
    public function setTotalCapacity(?int $totalCapacity): void
    {
        $this->totalCapacity = $totalCapacity;
    }

    /**
     * @return int|null
     */
    public function getBreakEven(): ?int
    {
        return $this->breakEven;
    }

    /**
     * @param int|null $breakEven
     */
    public function setBreakEven(?int $breakEven): void
    {
        $this->breakEven = $breakEven;
    }

    /**
     * @return int|null
     */
    public function getCapacityYellow(): ?int
    {
        return $this->capacityYellow;
    }

    /**
     * @param int|null $capacityYellow
     */
    public function setCapacityYellow(?int $capacityYellow): void
    {
        $this->capacityYellow = $capacityYellow;
    }

    /**
     * @return int|null
     */
    public function getOccupancy(): ?int
    {
        return $this->occupancy;
    }

    /**
     * @param int|null $occupancy
     */
    public function setOccupancy(?int $occupancy): void
    {
        $this->occupancy = $occupancy;
    }

    /**
     * @return int|null
     */
    public function getMoveInsRespite(): ?int
    {
        return $this->moveInsRespite;
    }

    /**
     * @param int|null $moveInsRespite
     */
    public function setMoveInsRespite(?int $moveInsRespite): void
    {
        $this->moveInsRespite = $moveInsRespite;
    }

    /**
     * @return int|null
     */
    public function getMoveOutsRespite(): ?int
    {
        return $this->moveOutsRespite;
    }

    /**
     * @param int|null $moveOutsRespite
     */
    public function setMoveOutsRespite(?int $moveOutsRespite): void
    {
        $this->moveOutsRespite = $moveOutsRespite;
    }

    /**
     * @return int|null
     */
    public function getMoveInsLongTerm(): ?int
    {
        return $this->moveInsLongTerm;
    }

    /**
     * @param int|null $moveInsLongTerm
     */
    public function setMoveInsLongTerm(?int $moveInsLongTerm): void
    {
        $this->moveInsLongTerm = $moveInsLongTerm;
    }

    /**
     * @return int|null
     */
    public function getMoveOutsLongTerm(): ?int
    {
        return $this->moveOutsLongTerm;
    }

    /**
     * @param int|null $moveOutsLongTerm
     */
    public function setMoveOutsLongTerm(?int $moveOutsLongTerm): void
    {
        $this->moveOutsLongTerm = $moveOutsLongTerm;
    }

    /**
     * @return int|null
     */
    public function getHotLeads(): ?int
    {
        return $this->hotLeads;
    }

    /**
     * @param int|null $hotLeads
     */
    public function setHotLeads(?int $hotLeads): void
    {
        $this->hotLeads = $hotLeads;
    }

    /**
     * @return int|null
     */
    public function getNoticeToVacate(): ?int
    {
        return $this->noticeToVacate;
    }

    /**
     * @param int|null $noticeToVacate
     */
    public function setNoticeToVacate(?int $noticeToVacate): void
    {
        $this->noticeToVacate = $noticeToVacate;
    }

    /**
     * @return int|null
     */
    public function getToursPerMonth(): ?int
    {
        return $this->toursPerMonth;
    }

    /**
     * @param int|null $toursPerMonth
     */
    public function setToursPerMonth(?int $toursPerMonth): void
    {
        $this->toursPerMonth = $toursPerMonth;
    }

    /**
     * @return int|null
     */
    public function getTotalInquiries(): ?int
    {
        return $this->totalInquiries;
    }

    /**
     * @param int|null $totalInquiries
     */
    public function setTotalInquiries(?int $totalInquiries): void
    {
        $this->totalInquiries = $totalInquiries;
    }

    /**
     * @return int|null
     */
    public function getQualifiedInquiries(): ?int
    {
        return $this->qualifiedInquiries;
    }

    /**
     * @param int|null $qualifiedInquiries
     */
    public function setQualifiedInquiries(?int $qualifiedInquiries): void
    {
        $this->qualifiedInquiries = $qualifiedInquiries;
    }

    /**
     * @return int
     */
    public function getOutreachPerMonth(): ?int
    {
        return $this->outreachPerMonth;
    }

    /**
     * @param int|null $outreachPerMonth
     */
    public function setOutreachPerMonth(?int $outreachPerMonth): void
    {
        $this->outreachPerMonth = $outreachPerMonth;
    }

    /**
     * @return int|null
     */
    public function getEventsPerMonth(): ?int
    {
        return $this->eventsPerMonth;
    }

    /**
     * @param int|null $eventsPerMonth
     */
    public function setEventsPerMonth(?int $eventsPerMonth): void
    {
        $this->eventsPerMonth = $eventsPerMonth;
    }

    /**
     * @return int|null
     */
    public function getAverageRoomRent(): ?int
    {
        return $this->averageRoomRent;
    }

    /**
     * @param int|null $averageRoomRent
     */
    public function setAverageRoomRent(?int $averageRoomRent): void
    {
        $this->averageRoomRent = $averageRoomRent;
    }
}
