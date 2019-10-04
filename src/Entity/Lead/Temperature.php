<?php

namespace App\Entity\Lead;

use App\Entity\Space;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class Temperature
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\TemperatureRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_lead_temperature_add",
 *          "api_lead_temperature_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_lead_temperature")
 * @Grid(
 *     api_lead_temperature_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "t.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "t.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "value",
 *              "type"       = "string",
 *              "field"      = "t.value"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class Temperature
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_temperature_list",
     *     "api_lead_temperature_get",
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_lead_temperature_add",
     *          "api_lead_temperature_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_temperature_add",
     *          "api_lead_temperature_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=120)
     * @Groups({
     *     "api_lead_temperature_list",
     *     "api_lead_temperature_get"
     * })
     */
    private $title;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_lead_temperature_add",
     *     "api_lead_temperature_edit"
     * })
     * @Assert\Regex(
     *      pattern="/^[1-9][0-9]*$/",
     *      message="The value should be numeric.",
     *      groups={
     *              "api_lead_temperature_add",
     *              "api_lead_temperature_edit"
     * })
     * @ORM\Column(name="value", type="integer")
     * @Groups({
     *     "api_lead_temperature_list",
     *     "api_lead_temperature_get"
     * })
     */
    private $value;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_lead_temperature_add",
     *     "api_lead_temperature_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="leadTemperatures")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_temperature_list",
     *     "api_lead_temperature_get"
     * })
     */
    private $space;

    public function getId()
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

    /**
     * @return int|null
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * @param int|null $value
     */
    public function setValue(?int $value): void
    {
        $this->value = $value;
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
}
