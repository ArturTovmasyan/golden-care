<?php

namespace App\Entity\Lead;

use App\Entity\Assessment\Form;
use App\Entity\Assessment\FormCategory;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Assessment
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\AssessmentRepository")
 * @ORM\Table(name="tbl_lead_assessment")
 * @Grid(
 *     api_lead_assessment_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "a.id"
 *          },
 *          {
 *              "id"         = "form",
 *              "type"       = "string",
 *              "field"      = "f.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "a.date"
 *          },
 *          {
 *              "id"         = "performed_by",
 *              "type"       = "string",
 *              "field"      = "a.performedBy"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(a.notes, 1, 100)), CASE WHEN LENGTH(a.notes) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "score",
 *              "type"       = "number",
 *              "field"      = "a.score"
 *          }
 *     }
 * )
 */
class Assessment
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_assessment_list",
     *     "api_lead_assessment_get"
     * })
     */
    private $id;

    /**
     * @var Lead
     * @Assert\NotNull(message = "Please select a Lead", groups={
     *          "api_lead_assessment_add",
     *          "api_lead_assessment_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Lead", inversedBy="assessments", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_lead", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $lead;

    /**
     * @var Form
     * @ORM\ManyToOne(targetEntity="App\Entity\Assessment\Form", inversedBy="leadAssessments")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_form", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Form",
     *      groups={
     *          "api_lead_assessment_edit",
     *          "api_lead_assessment_add"
     *      }
     * )
     * @Groups({
     *     "api_lead_assessment_list",
     *     "api_lead_assessment_get"
     * })
     */
    private $form;

    /**
     * @var \Datetime
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\NotBlank(
     *      message = "Please select a Date",
     *      groups={
     *          "api_lead_assessment_edit",
     *          "api_lead_assessment_add"
     *      }
     * )
     * @Groups({
     *     "api_lead_assessment_list",
     *     "api_lead_assessment_get"
     * })
     */
    private $date;

    /**
     * @var string
     * @ORM\Column(name="performed_by", type="string")
     * @Assert\NotBlank(
     *     message = "Value cannot be blank",
     *     groups={
     *          "api_lead_assessment_edit",
     *          "api_lead_assessment_add"
     *     }
     * )
     * @Groups({
     *     "api_lead_assessment_list",
     *     "api_lead_assessment_get"
     * })
     */
    private $performedBy;

    /**
     * @var string
     * @ORM\Column(name="notes", type="text", length=400, nullable=true)
     * @Assert\Length(
     *      max = 400,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_assessment_add",
     *          "api_lead_assessment_edit"
     *      }
     * )
     * @Groups({
     *     "api_lead_assessment_list",
     *     "api_lead_assessment_get"
     * })
     */
    private $notes;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AssessmentRow", mappedBy="assessment", cascade={"persist"})
     * @Groups({
     *     "api_lead_assessment_list"
     * })
     */
    private $assessmentRows;

    /**
     * @var float
     * @ORM\Column(name="score", type="decimal", precision=8, scale=2)
     * @Assert\NotNull(
     *      groups={
     *          "api_lead_assessment_edit",
     *          "api_lead_assessment_add"
     *      }
     * )
     * @Groups({
     *      "api_lead_assessment_list",
     *      "api_lead_assessment_get"
     * })
     */
    private $score = 0;

    /**
     * @return int
     */
    public function getId(): ?int
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
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @param Form $form
     */
    public function setForm(Form $form): void
    {
        $this->form = $form;
    }

    /**
     * @return \Datetime
     */
    public function getDate(): \Datetime
    {
        return $this->date;
    }

    /**
     * @param \Datetime $date
     */
    public function setDate(\Datetime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getPerformedBy(): string
    {
        return $this->performedBy;
    }

    /**
     * @param string $performedBy
     */
    public function setPerformedBy(string $performedBy): void
    {
        $this->performedBy = $performedBy;
    }

    /**
     * @return string
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     */
    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @return Collection|null
     */
    public function getAssessmentRows(): ?Collection
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
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("rows")
     * @Groups({
     *     "api_lead_assessment_get"
     * })
     */
    public function getVirtualRows(): ?array
    {
        $categories = [];

        $form_categories = $this->form->getFormCategories();
        /** @var FormCategory $form_category */
        foreach ($form_categories as $form_category) {
            $categoryItem = $form_category->getCategory();
            $categories[$categoryItem->getId()] = [
                'multi' => $categoryItem->isMultiItem(),
                'rows' => []
            ];
        }

        /** @var AssessmentRow $assessmentRow */
        foreach ($this->assessmentRows as $assessmentRow) {
            if (array_key_exists($assessmentRow->getRow()->getCategory()->getId(), $categories)) {
                $categories[$assessmentRow->getRow()->getCategory()->getId()]['rows'][] = $assessmentRow->getRow()->getId();
            }
        }

        $rows = [];
        foreach ($categories as $category) {
            if ($category['multi']) {
                $rows[] = $category['rows'];
            } else {
                $rows[] = \count($category['rows']) > 0 ? $category['rows'][0] : null;
            }
        }

        return $rows;
    }
}
