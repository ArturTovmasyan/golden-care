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
 * Class ResidentMedicationAllergy
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentMedicationAllergyRepository")
 * @UniqueEntity(
 *     fields={"resident", "medication"},
 *     errorPath="medication_id",
 *     message="The value is already in use for this Resident.",
 *     groups={
 *          "api_admin_resident_medication_allergy_add",
 *          "api_admin_resident_medication_allergy_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_resident_medication_allergy")
 * @Grid(
 *     api_admin_resident_medication_allergy_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rma.id"
 *          },
 *          {
 *              "id"         = "medication",
 *              "type"       = "string",
 *              "field"      = "m.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "notes",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(rma.notes, 1, 100)), CASE WHEN LENGTH(rma.notes) > 100 THEN '…' ELSE '' END)"
 *          }
 *     }
 * )
 */
class ResidentMedicationAllergy
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_medication_allergy_grid",
     *     "api_admin_resident_medication_allergy_list",
     *     "api_admin_resident_medication_allergy_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_medication_allergy_add",
     *     "api_admin_resident_medication_allergy_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentMedicationAllergies")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_medication_allergy_grid",
     *     "api_admin_resident_medication_allergy_list",
     *     "api_admin_resident_medication_allergy_get"
     * })
     */
    private $resident;

    /**
     * @var Medication
     * @Assert\NotNull(message = "Please select Medication", groups={
     *     "api_admin_resident_medication_allergy_add",
     *     "api_admin_resident_medication_allergy_edit"
     * })
     * @Assert\Valid(groups={
     *     "api_admin_resident_medication_allergy_add",
     *     "api_admin_resident_medication_allergy_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Medication", inversedBy="residentMedicationAllergies", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_medication", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_medication_allergy_grid",
     *     "api_admin_resident_medication_allergy_list",
     *     "api_admin_resident_medication_allergy_get"
     * })
     */
    private $medication;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_medication_allergy_add",
     *          "api_admin_resident_medication_allergy_edit"
     * })
     * @Groups({
     *     "api_admin_resident_medication_allergy_grid",
     *     "api_admin_resident_medication_allergy_list",
     *     "api_admin_resident_medication_allergy_get"
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
     * @return Medication|null
     */
    public function getMedication(): ?Medication
    {
        return $this->medication;
    }

    /**
     * @param Medication|null $medication
     */
    public function setMedication(?Medication $medication): void
    {
        $this->medication = $medication;
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
