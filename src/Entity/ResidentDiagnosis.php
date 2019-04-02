<?php

namespace App\Entity;

use App\Model\DiagnosisType;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentDiagnosis
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentDiagnosisRepository")
 * @UniqueEntity(
 *     fields={"resident", "diagnosis"},
 *     errorPath="diagnosis_id",
 *     message="This value is already in use for this resident.",
 *     groups={
 *          "api_admin_resident_diagnosis_add",
 *          "api_admin_resident_diagnosis_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_resident_diagnosis")
 * @Grid(
 *     api_admin_resident_diagnosis_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rd.id"
 *          },
 *          {
 *              "id"         = "diagnosis",
 *              "type"       = "string",
 *              "field"      = "d.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "type",
 *              "type"       = "enum",
 *              "field"      = "rd.type",
 *              "values"     = "\App\Model\DiagnosisType::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "rd.notes"
 *          }
 *     }
 * )
 */
class ResidentDiagnosis
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_diagnosis_grid",
     *     "api_admin_resident_diagnosis_list",
     *     "api_admin_resident_diagnosis_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_diagnosis_add",
     *     "api_admin_resident_diagnosis_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentDiagnoses")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_diagnosis_grid",
     *     "api_admin_resident_diagnosis_list",
     *     "api_admin_resident_diagnosis_get"
     * })
     */
    private $resident;

    /**
     * @var Diagnosis
     * @Assert\NotNull(message = "Please select a Diagnosis", groups={
     *     "api_admin_resident_diagnosis_add",
     *     "api_admin_resident_diagnosis_edit"
     * })
     * @Assert\Valid(groups={
     *     "api_admin_resident_diagnosis_add",
     *     "api_admin_resident_diagnosis_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Diagnosis", inversedBy="residentDiagnoses", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_diagnosis", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_diagnosis_grid",
     *     "api_admin_resident_diagnosis_list",
     *     "api_admin_resident_diagnosis_get"
     * })
     */
    private $diagnosis;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_diagnosis_add",
     *     "api_admin_resident_diagnosis_edit"
     * })
     * @Assert\Choice(
     *     callback={"App\Model\DiagnosisType","getTypeValues"},
     *     groups={
     *         "api_admin_resident_diagnosis_add",
     *         "api_admin_resident_diagnosis_edit"
     * })
     * @ORM\Column(name="type", type="integer", length=1)
     * @Groups({
     *     "api_admin_resident_diagnosis_grid",
     *     "api_admin_resident_diagnosis_list",
     *     "api_admin_resident_diagnosis_get"
     * })
     */
    private $type = DiagnosisType::PRIMARY;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_diagnosis_add",
     *          "api_admin_resident_diagnosis_edit"
     * })
     * @Groups({
     *     "api_admin_resident_diagnosis_grid",
     *     "api_admin_resident_diagnosis_list",
     *     "api_admin_resident_diagnosis_get"
     * })
     */
    private $notes;

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
     * @return Resident|null
     */
    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident|null $resident
     */
    public function setResident(?Resident $resident): void
    {
        $this->resident = $resident;
    }

    /**
     * @return Diagnosis|null
     */
    public function getDiagnosis(): ?Diagnosis
    {
        return $this->diagnosis;
    }

    /**
     * @param Diagnosis|null $diagnosis
     */
    public function setDiagnosis(?Diagnosis $diagnosis): void
    {
        $this->diagnosis = $diagnosis;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }
}
