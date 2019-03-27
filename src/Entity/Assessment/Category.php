<?php

namespace App\Entity\Assessment;

use App\Entity\Space;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;

/**
 * @ORM\Table(name="tbl_assessment_category")
 * @ORM\Entity(repositoryClass="App\Repository\Assessment\CategoryRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space.",
 *     groups={
 *          "api_admin_assessment_category_add",
 *          "api_admin_assessment_category_edit"
 *     }
 * )
 * @Grid(
 *     api_admin_assessment_category_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ac.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "ac.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "multi_item",
 *              "type"       = "number",
 *              "field"      = "ac.multiItem"
 *          }
 *     }
 * )
 */
class Category
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *      "api_admin_assessment_category_list",
     *      "api_admin_assessment_category_get",
     *      "api_admin_assessment_form_list",
     *      "api_admin_assessment_form_get",
     *      "api_admin_resident_assessment_list",
     *      "api_admin_resident_assessment_get",
     *      "api_admin_resident_assessment_report"
     * })
     */
    private $id;

    /**
     * @var Space
     * @Assert\NotNull(
     *      message = "Please select a Space",
     *      groups={
     *          "api_admin_assessment_category_add",
     *          "api_admin_assessment_category_edit"
     *      }
     * )
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="assessmentCategories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *      "api_admin_assessment_category_list",
     *      "api_admin_assessment_category_get",
     *      "api_admin_resident_assessment_list",
     *      "api_admin_resident_assessment_get",
     *      "api_admin_resident_assessment_report"
     * })
     */
    private $space;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", nullable=false)
     * @Assert\NotBlank(
     *     message="Assessment category title should not be blank.",
     *     groups={
     *          "api_admin_assessment_category_add",
     *          "api_admin_assessment_category_edit"
     *     }
     * )
     * @Groups({
     *      "api_admin_assessment_category_list",
     *      "api_admin_assessment_category_get",
     *      "api_admin_assessment_form_list",
     *      "api_admin_assessment_form_get",
     *      "api_admin_resident_assessment_list",
     *      "api_admin_resident_assessment_get",
     *      "api_admin_resident_assessment_report"
     * })
     */
    private $title;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Assessment\Row", mappedBy="category", cascade={"persist"})
     * @ORM\OrderBy({"orderNumber" = "ASC"})
     * @Groups({
     *      "api_admin_assessment_category_list",
     *      "api_admin_assessment_category_get",
     *      "api_admin_assessment_form_list",
     *      "api_admin_assessment_form_get",
     *      "api_admin_resident_assessment_list",
     *      "api_admin_resident_assessment_get",
     *      "api_admin_resident_assessment_report"
     * })
     */
    private $rows;

    /**
     * @var bool
     * @ORM\Column(name="multi_item", type="boolean", nullable=false)
     * @Assert\NotNull(
     *     groups={
     *          "api_admin_assessment_category_add",
     *          "api_admin_assessment_category_edit"
     *     }
     * )
     * @Groups({
     *      "api_admin_assessment_form_list",
     *      "api_admin_assessment_form_get",
     *      "api_admin_assessment_category_list",
     *      "api_admin_assessment_category_get",
     *      "api_admin_resident_assessment_list",
     *      "api_admin_resident_assessment_get",
     *      "api_admin_resident_assessment_report"
     * })
     */
    private $multiItem = false;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Assessment\FormCategory", mappedBy="category", cascade={"remove", "persist"})
     */
    private $formCategories;

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->rows = new ArrayCollection();
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
     * @return ArrayCollection
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @param ArrayCollection $rows
     */
    public function setRows(ArrayCollection $rows): void
    {
        $this->rows = $rows;
    }

    /**
     * @param Row $row
     */
    public function removeRow(Row $row)
    {
        $this->rows->removeElement($row);
    }

    /**
     * @return bool
     */
    public function isMultiItem(): bool
    {
        return $this->multiItem;
    }

    /**
     * @param bool $multiItem
     */
    public function setMultiItem(bool $multiItem): void
    {
        $this->multiItem = $multiItem;
    }

    /**
     * @return ArrayCollection
     */
    public function getFormCategories(): ArrayCollection
    {
        return $this->formCategories;
    }

    /**
     * @param ArrayCollection $formCategories
     */
    public function setFormCategories(ArrayCollection $formCategories): void
    {
        $this->formCategories = $formCategories;
    }
}
