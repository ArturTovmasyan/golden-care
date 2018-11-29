<?php

namespace App\Entity\Assessment;

use App\Entity\Space;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class CareLevelGroup
 *
 * @ORM\Entity(repositoryClass="App\Repository\Assessment\CareLevelGroupRepository")
 * @ORM\Table(name="tbl_assessment_care_level_group")
 * @Grid(
 *     api_admin_assessment_care_level_group_grid={
 *          {"id", "number", true, true, "aclg.id"},
 *          {"title", "string", true, true, "aclg.title"}
 *     }
 * )
 */
class CareLevelGroup
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_assessment_care_level_group_list",
     *     "api_admin_assessment_care_level_group_get",
     *     "api_admin_assessment_care_level_list",
     *     "api_admin_assessment_care_level_get"
     * })
     */
    private $id;

    /**
     * @var Space
     * @Assert\NotNull(
     *      message = "Please select a Space",
     *      groups={
     *          "api_admin_assessment_care_level_group_add",
     *          "api_admin_assessment_care_level_group_edit"
     *      }
     * )
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *      "api_admin_assessment_care_level_group_list",
     *      "api_admin_assessment_care_level_group_get"
     * })
     */
    private $space;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_care_level_add", "api_admin_care_level_edit"})
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_assessment_care_level_group_edit",
     *          "api_admin_assessment_care_level_group_add"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_assessment_care_level_group_list",
     *     "api_admin_assessment_care_level_group_get",
     *     "api_admin_assessment_care_level_list",
     *     "api_admin_assessment_care_level_get"
     * })
     */
    private $title;

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
}
