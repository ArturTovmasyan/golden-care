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
 * Class MedicationFormFactor
 *
 * @ORM\Entity(repositoryClass="App\Repository\MedicationFormFactorRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space.",
 *     groups={
 *          "api_admin_medication_form_factor_add",
 *          "api_admin_medication_form_factor_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_medication_form_factor")
 * @Grid(
 *     api_admin_medication_form_factor_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "mff.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "mff.title",
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
class MedicationFormFactor
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_medication_form_factor_list",
     *     "api_admin_medication_form_factor_get",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_medication_form_factor_add",
     *     "api_admin_medication_form_factor_edit"
     * })
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_medication_form_factor_add",
     *          "api_admin_medication_form_factor_edit"
     * })
     * @ORM\Column(name="title", type="string", length=200)
     * @Groups({
     *     "api_admin_medication_form_factor_grid",
     *     "api_admin_medication_form_factor_list",
     *     "api_admin_medication_form_factor_get",
     *     "api_admin_resident_medication_list",
     *     "api_admin_resident_medication_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_medication_form_factor_add",
     *     "api_admin_medication_form_factor_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_medication_form_factor_grid",
     *     "api_admin_medication_form_factor_list",
     *     "api_admin_medication_form_factor_get"
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

    /**
     * @return Space|null
     */
    public function getSpace(): ?Space
    {
        return $this->space;
    }

    /**
     * @param Space|null $space
     * @return MedicationFormFactor
     */
    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }
}
