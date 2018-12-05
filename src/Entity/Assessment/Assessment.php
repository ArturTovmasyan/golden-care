<?php

namespace App\Entity\Assessment;

use App\Entity\ResidentAssessment;
use App\Entity\Space;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Form
 *
 * @ORM\Entity(repositoryClass="App\Repository\Assessment\AssessmentRepository")
 * @ORM\Table(name="tbl_assessment")
 * @Grid(
 *     api_admin_assessment_grid={
 *          {"id", "number", true, true, "a.id"},
 *          {"form", "string", true, true, "f.title"},
 *          {"date", "string", true, true, "a.date"},
 *          {"performed_by", "string", true, true, "a.performedBy"},
 *          {"notes", "string", true, true, "a.notes"},
 *          {"score", "number", true, true, "a.score"}
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
     *     "api_admin_assessment_list",
     *     "api_admin_assessment_get",
     *     "api_admin_assessment_report"
     * })
     */
    private $id;

    /**
     * @var Space
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Space",
     *      groups={
     *          "api_admin_assessment_edit",
     *          "api_admin_assessment_add"
     *      }
     * )
     */
    private $space;

    /**
     * @var Form
     * @ORM\ManyToOne(targetEntity="App\Entity\Assessment\Form")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_form", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Form",
     *      groups={
     *          "api_admin_assessment_edit",
     *          "api_admin_assessment_add"
     *      }
     * )
     * @Groups({
     *     "api_admin_assessment_list",
     *     "api_admin_assessment_report",
     *     "api_admin_assessment_get"
     * })
     */
    private $form;

    /**
     * @var \Datetime
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\NotBlank(
     *      message = "Please select a Date",
     *      groups={
     *          "api_admin_assessment_edit",
     *          "api_admin_assessment_add"
     *      }
     * )
     * @Groups({
     *     "api_admin_assessment_list",
     *     "api_admin_assessment_report",
     *     "api_admin_assessment_get"
     * })
     */
    private $date;

    /**
     * @var string
     * @ORM\Column(name="performed_by", type="string", nullable=false)
     * @Assert\NotBlank(
     *     message = "This value can't be blank",
     *     groups={
     *          "api_admin_assessment_edit",
     *          "api_admin_assessment_add"
     *     }
     * )
     * @Groups({
     *     "api_admin_assessment_list",
     *     "api_admin_assessment_report",
     *     "api_admin_assessment_get"
     * })
     */
    private $performedBy;

    /**
     * @var string
     * @ORM\Column(name="notes", type="text", length=400, nullable=true)
     * @Groups({
     *     "api_admin_assessment_list",
     *     "api_admin_assessment_report",
     *     "api_admin_assessment_get"
     * })
     */
    private $notes;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AssessmentRow", mappedBy="assessment", cascade={"persist"})
     * @Groups({
     *     "api_admin_assessment_list",
     *     "api_admin_assessment_report",
     *     "api_admin_assessment_get"
     * })
     */
    private $assessmentRows;

    /**
     * @var float
     * @ORM\Column(name="score", type="decimal", precision=8, scale=2, nullable=false)
     * @Assert\NotNull(
     *      groups={
     *          "api_admin_assessment_edit",
     *          "api_admin_assessment_add"
     *      }
     * )
     * @Groups({
     *      "api_admin_assessment_list",
     *      "api_admin_assessment_report",
     *      "api_admin_assessment_get"
     * })
     */
    private $score = 0;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ResidentAssessment", mappedBy="assessment", cascade={"persist"})
     */
    private $residentAssessment;

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
    public function getNotes(): string
    {
        return $this->notes;
    }

    /**
     * @param string $notes
     */
    public function setNotes(string $notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @return ArrayCollection
     */
    public function getAssessmentRows()
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
     * @return ResidentAssessment
     */
    public function getResidentAssessment()
    {
        return $this->residentAssessment;
    }

    /**
     * @param mixed $residentAssessment
     */
    public function setResidentAssessment($residentAssessment): void
    {
        $this->residentAssessment = $residentAssessment;
    }
}
