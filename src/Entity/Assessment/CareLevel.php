<?php

namespace App\Entity\Assessment;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class CareLevel
 *
 * @ORM\Entity(repositoryClass="App\Repository\Assessment\CareLevelRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space.",
 *     groups={
 *          "api_admin_assessment_care_level_add",
 *          "api_admin_assessment_care_level_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_assessment_care_level")
 * @Grid(
 *     api_admin_assessment_care_level_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "acl.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "acl.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "level_low",
 *              "type"       = "string",
 *              "field"      = "acl.levelLow"
 *          },
 *          {
 *              "id"         = "level_high",
 *              "type"       = "string",
 *              "field"      = "acl.levelHigh"
 *          },
 *          {
 *              "id"         = "care_level_group",
 *              "type"       = "string",
 *              "field"      = "aclg.title"
 *          }
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
     *     "api_admin_resident_assessment_report"
     * })
     */
    private $id;

    /**
     * @var CareLevelGroup
     * @ORM\ManyToOne(targetEntity="App\Entity\Assessment\CareLevelGroup", inversedBy="careLevels", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_care_level_group", referencedColumnName="id", onDelete="CASCADE")
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
     * @Assert\NotBlank(groups={
     *     "api_admin_care_level_add",
     *     "api_admin_care_level_edit"
     * })
     * @Groups({
     *     "api_admin_assessment_care_level_list",
     *     "api_admin_assessment_care_level_get",
     *     "api_admin_resident_assessment_report"
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
     *     "api_admin_resident_assessment_report"
     * })
     */
    private $levelLow = 0;

    /**
     * @var int
     * @ORM\Column(name="level_high", type="integer", nullable=true)
     * @Groups({
     *     "api_admin_assessment_care_level_list",
     *     "api_admin_assessment_care_level_get",
     *     "api_admin_resident_assessment_report"
     * })
     */
    private $levelHigh;

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
    public function getLevelHigh(): ?int
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
