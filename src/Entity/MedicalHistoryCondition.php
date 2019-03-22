<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class MedicalHistoryCondition
 *
 * @ORM\Entity(repositoryClass="App\Repository\MedicalHistoryConditionRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space.",
 *     groups={
 *          "api_admin_medical_history_condition_add",
 *          "api_admin_medical_history_condition_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_medical_history_condition")
 * @Grid(
 *     api_admin_medical_history_condition_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "mhc.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "mhc.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "mhc.description"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class MedicalHistoryCondition
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_medical_history_condition_list",
     *     "api_admin_medical_history_condition_get",
     *     "api_admin_resident_medical_history_condition_list",
     *     "api_admin_resident_medical_history_condition_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *      groups={
     *          "api_admin_medical_history_condition_add",
     *          "api_admin_medical_history_condition_edit",
     *          "api_admin_resident_medical_history_condition_add",
     *          "api_admin_resident_medical_history_condition_edit"
     *      }     * )
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_medical_history_condition_add",
     *          "api_admin_medical_history_condition_edit",
     *          "api_admin_resident_medical_history_condition_add",
     *          "api_admin_resident_medical_history_condition_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=200)
     * @Groups({
     *     "api_admin_medical_history_condition_grid",
     *     "api_admin_medical_history_condition_list",
     *     "api_admin_medical_history_condition_get",
     *     "api_admin_resident_medical_history_condition_list",
     *     "api_admin_resident_medical_history_condition_get"
     * })
     */
    private $title;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=255, nullable=true)
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_medical_history_condition_add",
     *          "api_admin_medical_history_condition_edit",
     *          "api_admin_resident_medical_history_condition_add",
     *          "api_admin_resident_medical_history_condition_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_medical_history_condition_grid",
     *     "api_admin_medical_history_condition_list",
     *     "api_admin_medical_history_condition_get"
     * })
     */
    private $description;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_medical_history_condition_add",
     *     "api_admin_medical_history_condition_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_medical_history_condition_grid",
     *     "api_admin_medical_history_condition_list",
     *     "api_admin_medical_history_condition_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentMedicalHistoryCondition", mappedBy="condition", cascade={"remove", "persist"})
     */
    private $historyConditions;


    public function getId()
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


    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
    public function getHistoryConditions(): ArrayCollection
    {
        return $this->historyConditions;
    }

    /**
     * @param ArrayCollection $historyConditions
     */
    public function setHistoryConditions(ArrayCollection $historyConditions): void
    {
        $this->historyConditions = $historyConditions;
    }
}
