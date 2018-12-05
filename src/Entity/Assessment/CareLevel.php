<?php

namespace App\Entity\Assessment;

use App\Entity\Space;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class CareLevel
 *
 * @ORM\Entity(repositoryClass="App\Repository\Assessment\CareLevelRepository")
 * @ORM\Table(name="tbl_assessment_care_level")
 * @Grid(
 *     api_admin_assessment_care_level_grid={
 *          {"id", "number", true, true, "acl.id"},
 *          {"title", "string", true, true, "acl.title"},
 *          {"level_low", "string", true, true, "acl.levelLow"},
 *          {"level_high", "string", true, true, "acl.levelHigh"},
 *          {"care_level_group", "string", true, true, "aclg.title"}
 *     }
 * )
 */
class CareLevel
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_assessment_care_level_list",
     *     "api_admin_assessment_care_level_get",
     *     "api_admin_assessment_report"
     * })
     */
    private $id;

    /**
     * @var Space
     * @Assert\NotNull(
     *      message = "Please select a Space",
     *      groups={
     *          "api_admin_assessment_care_level_add",
     *          "api_admin_assessment_care_level_edit"
     *      }
     * )
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *      "api_admin_assessment_care_level_list",
     *      "api_admin_assessment_care_level_get"
     * })
     */
    private $space;

    /**
     * @var CareLevelGroup
     * @ORM\ManyToOne(targetEntity="App\Entity\Assessment\CareLevelGroup", inversedBy="careLevels", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_care_level_group", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Assert\NotNull(
     *      message = "Please select a CareLevelGroup",
     *      groups={
     *          "api_admin_assessment_care_level_add",
     *          "api_admin_assessment_care_level_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_assessment_care_level_list",
     *     "api_admin_assessment_care_level_get"
     * })
     */
    private $careLevelGroup;

    /**
     * @var string
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_assessment_care_level_edit",
     *          "api_admin_assessment_care_level_add"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank(groups={"api_admin_care_level_add", "api_admin_care_level_edit"})
     * @Groups({
     *     "api_admin_assessment_care_level_list",
     *     "api_admin_assessment_care_level_get",
     *     "api_admin_assessment_report"
     * })
     */
    private $title;

    /**
     * @var int
     * @ORM\Column(name="level_low", type="integer", nullable=false)
     * @Assert\Type(type="integer",message="The value {{ value }} is not a valid {{ type }}.")
     * @Groups({
     *     "api_admin_assessment_care_level_list",
     *     "api_admin_assessment_care_level_get",
     *     "api_admin_assessment_report"
     * })
     */
    private $levelLow = 0;

    /**
     * @var int
     * @ORM\Column(name="level_high", type="integer", nullable=true)
     * @Groups({
     *     "api_admin_assessment_care_level_list",
     *     "api_admin_assessment_care_level_get",
     *     "api_admin_assessment_report"
     * })
     */
    private $levelHigh;

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
     * @return Space
     */
    public function getSpace(): Space
    {
        return $this->space;
    }

    /**
     * @param Space $space
     */
    public function setSpace(Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return CareLevelGroup
     */
    public function getCareLevelGroup(): CareLevelGroup
    {
        return $this->careLevelGroup;
    }

    /**
     * @param CareLevelGroup $careLevelGroup
     */
    public function setCareLevelGroup(CareLevelGroup $careLevelGroup): void
    {
        $this->careLevelGroup = $careLevelGroup;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getLevelLow(): int
    {
        return $this->levelLow;
    }

    /**
     * @param int $levelLow
     */
    public function setLevelLow(int $levelLow): void
    {
        $this->levelLow = $levelLow;
    }

    /**
     * @return int
     */
    public function getLevelHigh(): int
    {
        return $this->levelHigh;
    }

    /**
     * @param int $levelHigh
     */
    public function setLevelHigh(int $levelHigh): void
    {
        $this->levelHigh = $levelHigh;
    }
}
