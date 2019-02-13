<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class Diagnosis
 *
 * @ORM\Entity(repositoryClass="App\Repository\DiagnosisRepository")
 * @ORM\Table(name="tbl_diagnosis")
 * @Grid(
 *     api_admin_diagnosis_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "d.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "d.title"
 *          },
 *          {
 *              "id"         = "acronym",
 *              "type"       = "string",
 *              "field"      = "d.acronym"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "d.description"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class Diagnosis
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_diagnosis_list",
     *     "api_admin_diagnosis_get",
     *     "api_admin_resident_diagnosis_list",
     *     "api_admin_resident_diagnosis_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_diagnosis_add",
     *     "api_admin_diagnosis_edit"
     * })
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_diagnosis_add",
     *          "api_admin_diagnosis_edit",
     *          "api_admin_resident_diagnosis_add",
     *          "api_admin_resident_diagnosis_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=200)
     * @Groups({
     *     "api_admin_diagnosis_grid",
     *     "api_admin_diagnosis_list",
     *     "api_admin_diagnosis_get",
     *     "api_admin_resident_diagnosis_list",
     *     "api_admin_resident_diagnosis_get"
     * })
     */
    private $title;

    /**
     * @var string
     * @Assert\Length(
     *      max = 20,
     *      maxMessage = "Acronym cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_diagnosis_add",
     *          "api_admin_diagnosis_edit",
     *          "api_admin_resident_diagnosis_add",
     *          "api_admin_resident_diagnosis_edit"
     *      }
     * )
     * @ORM\Column(name="acronym", type="string", length=20, nullable=true)
     * @Groups({"api_admin_diagnosis_grid", "api_admin_diagnosis_list", "api_admin_diagnosis_get"})
     */
    private $acronym;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=255, nullable=true)
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_diagnosis_add",
     *          "api_admin_diagnosis_edit",
     *          "api_admin_resident_diagnosis_add",
     *          "api_admin_resident_diagnosis_edit"
     *      }
     * )
     * @Groups({"api_admin_diagnosis_grid", "api_admin_diagnosis_list", "api_admin_diagnosis_get"})
     */
    private $description;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_diagnosis_add",
     *     "api_admin_diagnosis_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_diagnosis_grid",
     *     "api_admin_diagnosis_list",
     *     "api_admin_diagnosis_get"
     * })
     */
    private $space;

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

    public function getAcronym(): ?string
    {
        return $this->acronym;
    }

    public function setAcronym(?string $acronym): void
    {
        $this->acronym = $acronym;
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
     * @return Diagnosis
     */
    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }
}
