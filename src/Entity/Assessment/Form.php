<?php

namespace App\Entity\Assessment;

use App\Entity\Space;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class Form
 *
 * @ORM\Entity(repositoryClass="App\Repository\Assessment\FormRepository")
 * @ORM\Table(name="tbl_assessment_form")
 * @Grid(
 *     api_admin_assessment_form_grid={
 *          {"id", "number", true, true, "af.id"},
 *          {"title", "string", true, true, "af.title"}
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
     *     "api_admin_assessment_form_get"
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="SET NULL")
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
     *          "api_admin_assessment_form_edit",
     *          "api_admin_assessment_form_add"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_assessment_form_list",
     *     "api_admin_assessment_form_get"
     * })
     */
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity="CareLevelGroup", mappedBy="forms", cascade={"persist", "remove"})
     * @Groups({
     *     "api_admin_assessment_form_list",
     *     "api_admin_assessment_form_get"
     * })
     */
    protected $careLevelGroups;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="FormCategory", mappedBy="form", cascade={"remove", "persist"})
     * @ORM\OrderBy({"orderNumber" = "ASC"})
     * @Groups({
     *     "api_admin_assessment_form_list",
     *     "api_admin_assessment_form_get"
     * })
     */
    private $formCategories;

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
}