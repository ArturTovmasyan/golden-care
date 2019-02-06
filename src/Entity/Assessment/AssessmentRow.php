<?php

namespace App\Entity\Assessment;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Assessment\AssessmentRowRepository")
 * @ORM\Table(name="tbl_assessment_assessment_row")
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
     *     "api_admin_resident_assessment_list",
     *     "api_admin_resident_assessment_get",
     *     "api_admin_resident_assessment_report"
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
     *      message = "Please select a Assessment",
     *      groups={
     *          "api_admin_resident_assessment_edit",
     *          "api_admin_resident_assessment_add"
     *      }
     * )
     */
    private $assessment;

    /**
     * @var Row
     * @ORM\ManyToOne(targetEntity="Row", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_row", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Row",
     *      groups={
     *          "api_admin_resident_assessment_edit",
     *          "api_admin_resident_assessment_add"
     *      }
     * )
     * @Groups({
     *      "api_admin_resident_assessment_list",
     *      "api_admin_resident_assessment_get",
     *     "api_admin_resident_assessment_report"
     * })
     */
    private $row;

    /**
     * @var float
     * @ORM\Column(name="score", type="decimal", precision=8, scale=2, nullable=false)
     * @Assert\NotNull(
     *      groups={
     *          "api_admin_resident_assessment_edit",
     *          "api_admin_resident_assessment_add"
     *      }
     * )
     * @Groups({
     *      "api_admin_resident_assessment_list",
     *      "api_admin_resident_assessment_get",
     *      "api_admin_resident_assessment_report"
     * })
     */
    private $score = 0;

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
