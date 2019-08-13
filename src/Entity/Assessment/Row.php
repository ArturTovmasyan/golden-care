<?php
namespace App\Entity\Assessment;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_assessment_row")
 * @ORM\Entity (repositoryClass="App\Repository\Assessment\RowRepository")
 */
class Row
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
     * @var Category
     * @ORM\ManyToOne(targetEntity="App\Entity\Assessment\Category", inversedBy="rows", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_category", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *     groups={
     *          "api_admin_assessment_row_add",
     *          "api_admin_assessment_row_edit"
     *     }
     * )
     * @Groups({
     *      "api_admin_assessment_category_list"
     * })
     */
    private $category;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", nullable=false)
     * @Assert\NotBlank(
     *      groups={
     *          "api_admin_assessment_row_add",
     *          "api_admin_assessment_row_edit"
     *      }
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
     * @var float
     * @ORM\Column(name="score", type="decimal", precision=8, scale=2, nullable=false)
     * @Assert\NotNull(
     *      groups={
     *          "api_admin_assessment_row_add",
     *          "api_admin_assessment_row_edit"
     *      }
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
    private $score = 0;

    /**
     * @var int
     * @ORM\Column(name="order_number", type="integer", nullable=false)
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
    private $orderNumber = 0;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AssessmentRow", mappedBy="row", cascade={"persist"})
     */
    private $assessmentRows;

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
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category): void
    {
        $this->category = $category;
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
     * @return float
     */
    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * @param float $score
     */
    public function setScore(float $score): void
    {
        $this->score = $score;
    }

    /**
     * @return int
     */
    public function getOrderNumber(): int
    {
        return $this->orderNumber;
    }

    /**
     * @param int $orderNumber
     */
    public function setOrderNumber(int $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return ArrayCollection
     */
    public function getAssessmentRows(): ArrayCollection
    {
        return $this->assessmentRows;
    }

    /**
     * @param ArrayCollection $assessmentRows
     */
    public function setAssessmentRows(ArrayCollection $assessmentRows): void
    {
        $this->assessmentRows = $assessmentRows;
    }
}
