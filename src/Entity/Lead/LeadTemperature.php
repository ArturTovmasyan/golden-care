<?php

namespace App\Entity\Lead;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class LeadTemperature
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\LeadTemperatureRepository")
 * @ORM\Table(name="tbl_lead_lead_temperature")
 * @Grid(
 *     api_lead_lead_temperature_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "lt.id"
 *          },
 *          {
 *              "id"         = "created_by",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(u.firstName, ''), ' ', COALESCE(u.lastName, ''))"
 *          },
 *          {
 *              "id"         = "temperature",
 *              "type"       = "string",
 *              "field"      = "t.title"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "lt.date"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(lt.notes, 1, 100)), CASE WHEN LENGTH(lt.notes) > 100 THEN '…' ELSE '' END)"
 *          }
 *     }
 * )
 */
class LeadTemperature
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_lead_temperature_list",
     *     "api_lead_lead_temperature_get"
     * })
     */
    private $id;

    /**
     * @var Lead
     * @Assert\NotNull(message = "Please select a Lead", groups={
     *          "api_lead_lead_temperature_add",
     *          "api_lead_lead_temperature_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Lead", inversedBy="leadTemperatures", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_lead", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_lead_temperature_list",
     *     "api_lead_lead_temperature_get"
     * })
     */
    private $lead;

    /**
     * @var Temperature
     * @Assert\NotNull(message = "Please select a Temperature", groups={
     *          "api_lead_lead_temperature_add",
     *          "api_lead_lead_temperature_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Temperature", inversedBy="leadTemperatures", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_temperature", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_lead_temperature_list",
     *     "api_lead_lead_temperature_get"
     * })
     */
    private $temperature;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *          "api_lead_lead_temperature_add",
     *          "api_lead_lead_temperature_edit"
     * })
     * @Assert\DateTime(groups={
     *          "api_lead_lead_temperature_add",
     *          "api_lead_lead_temperature_edit"
     * })
     * @ORM\Column(name="date", type="datetime")
     * @Groups({
     *     "api_lead_lead_temperature_list",
     *     "api_lead_lead_temperature_get"
     * })
     */
    private $date;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_lead_temperature_add",
     *          "api_lead_lead_temperature_edit"
     * })
     * @Groups({
     *     "api_lead_lead_temperature_list",
     *     "api_lead_lead_temperature_get"
     * })
     */
    private $notes;

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Lead|null
     */
    public function getLead(): ?Lead
    {
        return $this->lead;
    }

    /**
     * @param Lead|null $lead
     */
    public function setLead(?Lead $lead): void
    {
        $this->lead = $lead;
    }

    /**
     * @return Temperature|null
     */
    public function getTemperature(): ?Temperature
    {
        return $this->temperature;
    }

    /**
     * @param Temperature|null $temperature
     */
    public function setTemperature(?Temperature $temperature): void
    {
        $this->temperature = $temperature;
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
     * @return null|string
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param null|string $notes
     */
    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }
}
