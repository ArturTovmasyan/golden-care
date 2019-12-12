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
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Form
 *
 * @ORM\Entity(repositoryClass="App\Repository\Assessment\FormRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_assessment_form_add",
 *          "api_admin_assessment_form_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_assessment_form")
 * @Grid(
 *     api_admin_assessment_form_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "af.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "af.title",
 *              "link"       = ":edit"
 *          }
 *     }
 * )
 */
class Form
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_assessment_form_list",
     *     "api_admin_assessment_form_get",
     *     "api_admin_resident_assessment_list",
     *     "api_admin_resident_assessment_get",
     *     "api_admin_resident_assessment_report",
     *     "api_lead_assessment_list",
     *     "api_lead_assessment_get"
     * })
     */
    private $id;

    /**
     * @var Space
     * @Assert\NotNull(
     *      message = "Please select a Space",
     *      groups={
     *          "api_admin_assessment_form_list",
     *          "api_admin_assessment_form_get"
     *      }
     * )
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="assessmentForms")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_assessment_form_list",
     *     "api_admin_assessment_form_get",
     *     "api_admin_resident_assessment_list",
     *     "api_admin_resident_assessment_get",
     *     "api_admin_resident_assessment_report",
     *     "api_lead_assessment_list",
     *     "api_lead_assessment_get"
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
     *          "api_admin_assessment_form_edit",
     *          "api_admin_assessment_form_add"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_assessment_form_list",
     *     "api_admin_assessment_form_get",
     *     "api_admin_resident_assessment_list",
     *     "api_admin_resident_assessment_get",
     *     "api_admin_resident_assessment_report",
     *     "api_lead_assessment_list",
     *     "api_lead_assessment_get"
     * })
     */
    private $title;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="CareLevelGroup", mappedBy="forms", cascade={"persist"})
     * @Groups({
     *     "api_admin_assessment_form_list",
     *     "api_admin_assessment_form_get",
     *     "api_admin_resident_assessment_report"
     * })
     */
    protected $careLevelGroups;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="FormCategory", mappedBy="form", cascade={"persist"})
     * @ORM\OrderBy({"orderNumber" = "ASC"})
     * @Groups({
     *     "api_admin_resident_assessment_list",
     *     "api_admin_resident_assessment_get",
     *     "api_admin_resident_assessment_report",
     *     "api_lead_assessment_list",
     *     "api_lead_assessment_get"
     * })
     */
    private $formCategories;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Assessment\Assessment", mappedBy="form", cascade={"remove", "persist"})
     */
    private $assessments;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Assessment", mappedBy="form", cascade={"remove", "persist"})
     */
    private $leadAssessments;

    /**
     * Form constructor.
     */
    public function __construct()
    {
        $this->careLevelGroups = new ArrayCollection();
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
    public function getCareLevelGroups()
    {
        return $this->careLevelGroups;
    }

    /**
     * @param mixed $careLevelGroups
     */
    public function setCareLevelGroups($careLevelGroups): void
    {
        $this->careLevelGroups = $careLevelGroups;

        foreach ($careLevelGroups as $careLevelGroup) {
            $careLevelGroup->addForm($this);
        }
    }

    /**
     * @param CareLevelGroup $careLevelGroup
     */
    public function addCareLevelGroup($careLevelGroup)
    {
        $careLevelGroup->addForm($this);
        $this->careLevelGroups[] = $careLevelGroup;
    }

    /**
     * @param CareLevelGroup $careLevelGroup
     */
    public function removeCareLevelGroup(CareLevelGroup $careLevelGroup)
    {
        $this->careLevelGroups->removeElement($careLevelGroup);
        $careLevelGroup->removeForm($this);
    }

    /**
     * @return ArrayCollection
     */
    public function getFormCategories()
    {
        return $this->formCategories;
    }

    /**
     * @param ArrayCollection $formCategories
     */
    public function setFormCategories($formCategories)
    {
        $this->formCategories = $formCategories;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("categories")
     * @Groups({
     *     "api_admin_assessment_form_list",
     *     "api_admin_assessment_form_get",
     * })
     */
    public function getVirtualCategories()
    {
        $categories = [];

        /** @var FormCategory $formCategory */
        foreach ($this->formCategories as $formCategory) {
            $categories[] = $formCategory->getCategory();
        }

        return $categories;
    }

    /**
     * @return ArrayCollection
     */
    public function getAssessments(): ArrayCollection
    {
        return $this->assessments;
    }

    /**
     * @param ArrayCollection $assessments
     */
    public function setAssessments(ArrayCollection $assessments): void
    {
        $this->assessments = $assessments;
    }

    /**
     * @return mixed
     */
    public function getLeadAssessments()
    {
        return $this->leadAssessments;
    }

    /**
     * @param mixed $leadAssessments
     */
    public function setLeadAssessments($leadAssessments): void
    {
        $this->leadAssessments = $leadAssessments;
    }
}
