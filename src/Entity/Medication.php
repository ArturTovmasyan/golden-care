<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Annotation\Grid as Grid;
use JMS\Serializer\Annotation\Groups;

/**
 * Class Medication.
 *
 * @ORM\Table(name="tbl_medication")
 * @ORM\Entity(repositoryClass="App\Repository\MedicationRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_medication_add",
 *          "api_admin_medication_edit"
 *     }
 * )
 * @Grid(
 *     api_admin_medication_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "m.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "m.title",
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
class Medication
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_medication_grid",
     *     "api_admin_medication_list",
     *     "api_admin_medication_get",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get",
     *     "api_admin_resident_medication_allergy_list",
     *     "api_admin_resident_medication_allergy_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @Groups({
     *     "api_admin_medication_grid",
     *     "api_admin_medication_list",
     *     "api_admin_medication_get",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get",
     *     "api_admin_resident_medication_allergy_list",
     *     "api_admin_resident_medication_allergy_get"
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_medication_add",
     *     "api_admin_medication_edit",
     *     "api_admin_resident_medication_allergy_add",
     *     "api_admin_resident_medication_allergy_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_medication_add",
     *          "api_admin_medication_edit",
     *          "api_admin_resident_medication_allergy_add",
     *          "api_admin_resident_medication_allergy_edit"
     *      }
     * )
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_medication_add",
     *     "api_admin_medication_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="medications")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_medication_grid",
     *     "api_admin_medication_list",
     *     "api_admin_medication_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentMedication", mappedBy="medication", cascade={"remove", "persist"})
     */
    private $residentMedications;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentMedicationAllergy", mappedBy="medication", cascade={"remove", "persist"})
     */
    private $residentMedicationAllergies;

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
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
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
    public function getResidentMedications(): ArrayCollection
    {
        return $this->residentMedications;
    }

    /**
     * @param ArrayCollection $residentMedications
     */
    public function setResidentMedications(ArrayCollection $residentMedications): void
    {
        $this->residentMedications = $residentMedications;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentMedicationAllergies(): ArrayCollection
    {
        return $this->residentMedicationAllergies;
    }

    /**
     * @param ArrayCollection $residentMedicationAllergies
     */
    public function setResidentMedicationAllergies(ArrayCollection $residentMedicationAllergies): void
    {
        $this->residentMedicationAllergies = $residentMedicationAllergies;
    }
}
