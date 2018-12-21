<?php

namespace App\Entity;

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
 *     fields="name",
 *     message="Sorry, this name is already in use.",
 *     groups={
 *          "api_admin_medication_add",
 *          "api_admin_medication_edit",
 *          "api_admin_resident_medication_allergy_add",
 *          "api_admin_resident_medication_allergy_edit"
 *      }
 * )
 * @Grid(
 *     api_admin_medication_grid={
 *          {"id", "number", true, true, "m.id"},
 *          {"title", "string", true, true, "m.title"},
 *          {"space", "string", true, true, "s.name"},
 *     },
 *     api_dashboard_medication_grid={
 *          {"id", "number", true, true, "m.id"},
 *          {"title", "string", true, true, "m.title"},
 *          {"space", "string", true, true, "s.name"},
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
     *     "api_dashboard_medication_grid",
     *     "api_dashboard_medication_list",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get",
     *     "api_admin_resident_medication_allergy_list",
     *     "api_admin_resident_medication_allergy_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=20, nullable=false)
     * @Groups({
     *     "api_admin_medication_grid",
     *     "api_admin_medication_list",
     *     "api_admin_medication_get",
     *     "api_dashboard_medication_grid",
     *     "api_dashboard_medication_list",
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
     *      max = 20,
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
     * @Assert\NotNull(message = "Please select a Space", groups={"api_admin_medication_add", "api_admin_medication_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_admin_medication_grid",
     *     "api_admin_medication_list",
     *     "api_admin_medication_get"
     * })
     */
    private $space;

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
     * @return Medication
     */
    public function setSpace(?Space $space): self
    {
        $this->space = $space;

        return $this;
    }
}
