<?php

namespace App\Entity\Assessment;

use App\Entity\Space;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class CareLevelGroup
 *
 * @ORM\Entity(repositoryClass="App\Repository\Assessment\CareLevelGroupRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_assessment_care_level_group_add",
 *          "api_admin_assessment_care_level_group_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_assessment_care_level_group")
 * @Grid(
 *     api_admin_assessment_care_level_group_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "aclg.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "aclg.title",
 *              "link"       = ":edit"
 *          }
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
     *     "api_admin_assessment_care_level_get",
     *     "api_admin_assessment_form_list",
     *     "api_admin_assessment_form_get",
     *     "api_admin_resident_assessment_report"
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="assessmentCareLevelGroups")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *      "api_admin_assessment_care_level_group_list",
     *      "api_admin_assessment_care_level_group_get"
     * })
     */
    private $space;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_care_level_add",
     *     "api_admin_care_level_edit"
     * })
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
     *     "api_admin_assessment_care_level_get",
     *     "api_admin_assessment_form_list",
     *     "api_admin_assessment_form_get",
     *     "api_admin_resident_assessment_report"
     * })
     */
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity="Form", inversedBy="careLevelGroups", cascade={"persist", "remove"})
     * @ORM\JoinTable(
     *      name="tbl_assessment_form_care_level_group",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_care_level_group", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_form", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     */
    private $forms;

    /**
     * @ORM\OneToMany(targetEntity="CareLevel", mappedBy="careLevelGroup", cascade={"persist"})
     * @Groups({
     *     "api_admin_resident_assessment_report"
     * })
     */
    private $careLevels;

    /**
     * CareLevelGroup constructor.
     */
    public function __construct()
    {
        $this->forms = new ArrayCollection();
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

    /**
     * @return mixed
     */
    public function getForms()
    {
        return $this->forms;
    }

    /**
     * @param mixed $forms
     */
    public function setForms($forms): void
    {
        $this->forms = $forms;
    }

    /**
     * @param Form $form
     */
    public function addForm($form)
    {
        $this->forms->add($form);
    }

    /**
     * @param Form $form
     */
    public function removeForm(Form $form)
    {
        $this->forms->removeElement($form);
    }

    /**
     * @return mixed
     */
    public function getCareLevels()
    {
        return $this->careLevels;
    }

    /**
     * @param mixed $careLevels
     */
    public function setCareLevels($careLevels): void
    {
        $this->careLevels = $careLevels;
    }
}
