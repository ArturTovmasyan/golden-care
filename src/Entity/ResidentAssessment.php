<?php

namespace App\Entity;

use App\Entity\Assessment\Assessment;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentAllergen
 *
 * @ORM\Entity()
 * @ORM\Table(name="tbl_resident_assessment")
 */
class ResidentAssessment
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_list",
     *     "api_admin_resident_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(
     *      message = "Please select a Resident",
     *      groups={
     *          "api_admin_assessment_assessment_add",
     *          "api_admin_assessment_assessment_edit"
     *      }
     * )
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentAssessments", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_list",
     *     "api_admin_resident_get"
     * })
     */
    private $resident;

    /**
     * @var Assessment
     * @Assert\NotNull(
     *      message = "Please select a Assessment",
     *      groups={
     *          "api_admin_assessment_assessment_add",
     *          "api_admin_assessment_assessment_edit"
     *      }
     * )
     * @Assert\Valid(
     *      groups={
     *          "api_admin_assessment_assessment_add",
     *          "api_admin_assessment_assessment_edit"
     *      }
     * )
     * @ORM\OneToOne(targetEntity="App\Entity\Assessment\Assessment", inversedBy="residentAssessment", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_assessment", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_list",
     *     "api_admin_resident_get"
     * })
     */
    private $assessment;

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
     * @return Resident
     */
    public function getResident(): Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident $resident
     */
    public function setResident(Resident $resident): void
    {
        $this->resident = $resident;
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
}
