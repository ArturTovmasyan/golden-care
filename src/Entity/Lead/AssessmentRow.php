<?php

namespace App\Entity\Lead;

use App\Entity\Assessment\Row;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Lead\AssessmentRowRepository")
 * @ORM\Table(name="tbl_lead_assessment_assessment_row")
 */
class AssessmentRow
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
     * @var Assessment
     * @ORM\ManyToOne(targetEntity="Assessment", inversedBy="assessmentRows", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_assessment", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select an Assessment",
     *      groups={
     *          "api_lead_assessment_row_edit",
     *          "api_lead_assessment_row_add"
     *      }
     * )
     */
    private $assessment;

    /**
     * @var Row
     * @ORM\ManyToOne(targetEntity="App\Entity\Assessment\Row", inversedBy="leadAssessmentRows", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_row", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Row",
     *      groups={
     *          "api_lead_assessment_row_edit",
     *          "api_lead_assessment_row_add"
     *      }
     * )
     * @Groups({
     *      "api_lead_assessment_list",
     *      "api_lead_assessment_get"
     * })
     */
    private $row;

    /**
     * @var float
     * @ORM\Column(name="score", type="decimal", precision=8, scale=2)
     * @Assert\NotNull(
     *      groups={
     *          "api_lead_assessment_row_edit",
     *          "api_lead_assessment_row_add"
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
     * @return Assessment
     */
    public function getAssessment(): Assessment
    {
        return $this->assessment;
    }

    /**
     * @param Assessment $assessment
     */
    public function setAssessment(Assessment $assessment): void
    {
        $this->assessment = $assessment;
    }

    /**
     * @return Row
     */
    public function getRow(): Row
    {
        return $this->row;
    }

    /**
     * @param Row $row
     */
    public function setRow(Row $row): void
    {
        $this->row = $row;
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
}
