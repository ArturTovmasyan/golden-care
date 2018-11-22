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
 *          {"id", "number", true, true, "d.id"},
 *          {"title", "string", true, true, "d.title"},
 *          {"acronym", "string", true, true, "d.acronym"},
 *          {"description", "string", true, true, "d.description"}
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
     * @Groups({"api_admin_diagnosis_list", "api_admin_diagnosis_get"})
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_diagnosis_add", "api_admin_diagnosis_edit"})
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_diagnosis_add", "api_admin_diagnosis_edit"}
     * )
     * @ORM\Column(name="title", type="string", length=200)
     * @Groups({"api_admin_diagnosis_grid", "api_admin_diagnosis_list", "api_admin_diagnosis_get"})
     */
    private $title;

    /**
     * @var string
     * @Assert\Length(
     *      max = 20,
     *      maxMessage = "Acronym cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_diagnosis_add", "api_admin_diagnosis_edit"}
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
     *      groups={"api_admin_care_level_add", "api_admin_care_level_edit"}
     * )
     * @Groups({"api_admin_diagnosis_grid", "api_admin_diagnosis_list", "api_admin_diagnosis_get"})
     */
    private $description;

    public function getId(): int
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

    public function setAcronym(?string $acronym): self
    {
        $this->acronym = $acronym;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}