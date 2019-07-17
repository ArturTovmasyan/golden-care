<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class HealthInsurance
 *
 * @ORM\Entity(repositoryClass="App\Repository\HealthInsuranceRepository")
 * @UniqueEntity(
 *     fields={"resident", "company"},
 *     errorPath="company_id",
 *     message="The value is already in use for this Resident.",
 *     groups={
 *          "api_admin_health_insurance_add",
 *          "api_admin_health_insurance_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_health_insurance")
 * @Grid(
 *     api_admin_health_insurance_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "hi.id"
 *          },
 *          {
 *              "id"         = "insurance_company",
 *              "type"       = "string",
 *              "field"      = "ic.title"
 *          },
 *          {
 *              "id"         = "medical_record_number",
 *              "type"       = "string",
 *              "field"      = "hi.medicalRecordNumber"
 *          },
 *          {
 *              "id"         = "group_number",
 *              "type"       = "string",
 *              "field"      = "hi.groupNumber"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "hi.notes"
 *          }
 *     }
 * )
 */
class HealthInsurance
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_health_insurance_list",
     *     "api_admin_health_insurance_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_health_insurance_add",
     *     "api_admin_health_insurance_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="healthInsurances")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_health_insurance_list",
     *     "api_admin_health_insurance_get"
     * })
     */
    private $resident;

    /**
     * @var InsuranceCompany
     * @Assert\NotNull(message = "Please select an Insurance Company", groups={
     *     "api_admin_health_insurance_add",
     *     "api_admin_health_insurance_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\InsuranceCompany", inversedBy="healthInsurances")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_company", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_health_insurance_list",
     *     "api_admin_health_insurance_get"
     * })
     */
    private $company;

    /**
     * @var string $medicalRecordNumber
     * @ORM\Column(name="medical_record_number", type="string", length=32)
     * @Assert\NotBlank(groups={
     *     "api_admin_health_insurance_add",
     *     "api_admin_health_insurance_edit"
     * })
     * @Assert\Regex(
     *     pattern="/^[A-Za-z0-9]+$/",
     *     message="The value should be alphanumeric.",
     *     groups={
     *         "api_admin_health_insurance_add",
     *         "api_admin_health_insurance_edit"
     * })
     * @Assert\Length(
     *      max = 32,
     *      maxMessage = "Medical Record Number cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_health_insurance_add",
     *          "api_admin_health_insurance_edit"
     * })
     * @Groups({
     *     "api_admin_health_insurance_list",
     *     "api_admin_health_insurance_get"
     * })
     */
    private $medicalRecordNumber;

    /**
     * @var string $groupNumber
     * @ORM\Column(name="group_number", type="string", length=32)
     * @Assert\NotBlank(groups={
     *     "api_admin_health_insurance_add",
     *     "api_admin_health_insurance_edit"
     * })
     * @Assert\Regex(
     *     pattern="/^[A-Za-z0-9]+$/",
     *     message="The value should be alphanumeric.",
     *     groups={
     *         "api_admin_health_insurance_add",
     *         "api_admin_health_insurance_edit"
     * })
     * @Assert\Length(
     *      max = 32,
     *      maxMessage = "Group Number cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_health_insurance_add",
     *          "api_admin_health_insurance_edit"
     * })
     * @Groups({
     *     "api_admin_health_insurance_list",
     *     "api_admin_health_insurance_get"
     * })
     */
    private $groupNumber;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_health_insurance_add",
     *          "api_admin_health_insurance_edit"
     * })
     * @Groups({
     *     "api_admin_health_insurance_list",
     *     "api_admin_health_insurance_get"
     * })
     */
    private $notes;

    /**
     * @var HealthInsuranceFile
     * @ORM\OneToOne(targetEntity="App\Entity\HealthInsuranceFile", mappedBy="insurance", cascade={"remove", "persist"})
     * @Groups({
     *     "api_admin_health_insurance_list",
     *     "api_admin_health_insurance_get"
     * })
     */
    private $file;

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
     * @return InsuranceCompany|null
     */
    public function getCompany(): ?InsuranceCompany
    {
        return $this->company;
    }

    /**
     * @param InsuranceCompany|null $company
     */
    public function setCompany(?InsuranceCompany $company): void
    {
        $this->company = $company;
    }

    /**
     * @return null|string
     */
    public function getMedicalRecordNumber(): ?string
    {
        return $this->medicalRecordNumber;
    }

    /**
     * @param null|string $medicalRecordNumber
     */
    public function setMedicalRecordNumber(?string $medicalRecordNumber): void
    {
        $this->medicalRecordNumber = $medicalRecordNumber;
    }

    /**
     * @return null|string
     */
    public function getGroupNumber(): ?string
    {
        return $this->groupNumber;
    }

    /**
     * @param null|string $groupNumber
     */
    public function setGroupNumber(?string $groupNumber): void
    {
        $this->groupNumber = $groupNumber;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @return HealthInsuranceFile|null
     */
    public function getFile(): ?HealthInsuranceFile
    {
        return $this->file;
    }

    /**
     * @param HealthInsuranceFile|null $file
     */
    public function setFile(?HealthInsuranceFile $file): void
    {
        $this->file = $file;
    }
}
