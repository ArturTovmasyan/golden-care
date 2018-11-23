<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentAllergen
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentAllergenRepository")
 * @ORM\Table(name="tbl_resident_allergen")
 * @Grid(
 *     api_admin_resident_allergen_grid={
 *          {"id", "number", true, true, "ra.id"},
 *          {"allergen", "string", true, true, "a.title"},
 *          {"notes", "string", true, true, "ra.notes"}
 *     }
 * )
 */
class ResidentAllergen
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_allergen_grid",
     *     "api_admin_resident_allergen_list",
     *     "api_admin_resident_allergen_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={"api_admin_resident_allergen_add", "api_admin_resident_allergen_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_allergen_grid", "api_admin_resident_allergen_list", "api_admin_resident_allergen_get"})
     */
    private $resident;

    /**
     * @var Allergen
     * @Assert\NotNull(message = "Please select a Allergen", groups={"api_admin_resident_allergen_add", "api_admin_resident_allergen_edit"})
     * @Assert\Valid(groups={"api_admin_resident_allergen_add", "api_admin_resident_allergen_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Allergen", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_allergen", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_allergen_grid", "api_admin_resident_allergen_list", "api_admin_resident_allergen_get"})
     */
    private $allergen;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_allergen_add", "api_admin_resident_allergen_edit"}
     * )
     * @Groups({"api_admin_resident_allergen_grid", "api_admin_resident_allergen_list", "api_admin_resident_allergen_get"})
     */
    private $notes;

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
     * @return Resident|null
     */
    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident|null $resident
     * @return ResidentAllergen
     */
    public function setResident(?Resident $resident): self
    {
        $this->resident = $resident;

        return $this;
    }

    /**
     * @return Allergen|null
     */
    public function getAllergen(): ?Allergen
    {
        return $this->allergen;
    }

    /**
     * @param Allergen|null $allergen
     * @return ResidentAllergen
     */
    public function setAllergen(?Allergen $allergen): self
    {
        $this->allergen = $allergen;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }
}
