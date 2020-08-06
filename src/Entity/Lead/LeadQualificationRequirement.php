<?php

namespace App\Entity\Lead;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Lead\LeadQualificationRequirementRepository")
 * @ORM\Table(name="tbl_lead_lead_qualification_requirement")
 * )
 */
class LeadQualificationRequirement
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_lead_qualification_requirement_get",
     *     "api_lead_lead_qualification_requirement_list"
     * })
     */
    private $id;

    /**
     * @var Lead
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Lead", inversedBy="leadQualificationRequirements", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_lead", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Lead",
     *      groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit",
     *          "api_lead_lead_qualification_edit"
     *      }
     * )
     */
    private $lead;

    /**
     * @var QualificationRequirement
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\QualificationRequirement", inversedBy="leadQualificationRequirements", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_qualification_requirement", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Qualification Requirement",
     *      groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit",
     *          "api_lead_lead_qualification_edit"
     *      }
     * )
     * @Groups({
     *      "api_lead_lead_qualification_requirement_get",
     *      "api_lead_lead_qualification_requirement_list",
     *      "api_lead_lead_list",
     *      "api_lead_lead_get"
     * })
     */
    private $qualificationRequirement;

    /**
     * @var int
     * @ORM\Column(name="qualified", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\Lead\Qualified","getTypeValues"},
     *     groups={
     *          "api_lead_lead_add",
     *          "api_lead_lead_edit",
     *          "api_lead_lead_qualification_edit"
     *     }
     * )
     * @Groups({
     *      "api_lead_lead_qualification_requirement_get",
     *      "api_lead_lead_qualification_requirement_list",
     *      "api_lead_lead_list",
     *      "api_lead_lead_get"
     * })
     */
    private $qualified;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
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
     * @return QualificationRequirement|null
     */
    public function getQualificationRequirement(): ?QualificationRequirement
    {
        return $this->qualificationRequirement;
    }

    /**
     * @param QualificationRequirement|null $qualificationRequirement
     */
    public function setQualificationRequirement(?QualificationRequirement $qualificationRequirement): void
    {
        $this->qualificationRequirement = $qualificationRequirement;
    }

    /**
     * @return int|null
     */
    public function getQualified(): ?int
    {
        return $this->qualified;
    }

    /**
     * @param int|null $qualified
     */
    public function setQualified(?int $qualified): void
    {
        $this->qualified = $qualified;
    }
}
