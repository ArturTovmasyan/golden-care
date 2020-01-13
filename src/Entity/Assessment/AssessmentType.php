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
 * Class AssessmentType
 *
 * @ORM\Entity(repositoryClass="App\Repository\Assessment\AssessmentTypeRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_assessment_type_add",
 *          "api_admin_assessment_type_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_assessment_type")
 * @Grid(
 *     api_admin_assessment_type_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "at.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "at.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class AssessmentType
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_assessment_type_list",
     *     "api_admin_assessment_type_get",
     *     "api_admin_resident_assessment_list",
     *     "api_admin_resident_assessment_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_assessment_type_add",
     *     "api_admin_assessment_type_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_assessment_type_add",
     *          "api_admin_assessment_type_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_assessment_type_list",
     *     "api_admin_assessment_type_get",
     *     "api_admin_resident_assessment_list",
     *     "api_admin_resident_assessment_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_assessment_type_add",
     *     "api_admin_assessment_type_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="assessmentTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_assessment_type_list",
     *     "api_admin_assessment_type_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Assessment\Assessment", mappedBy="type", cascade={"remove", "persist"})
     */
    private $assessments;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $title = preg_replace('/\s\s+/', ' ', $title);
        $this->title = $title;
    }

    /**
     * @return Space|null
     */
    public function getSpace(): ?Space
    {
        return $this->space;
    }

    /**
     * @param Space|null $space
     */
    public function setSpace(?Space $space): void
    {
        $this->space = $space;
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
}
