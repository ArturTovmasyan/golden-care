<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class MedicationFormFactor
 *
 * @ORM\Entity(repositoryClass="App\Repository\MedicationFormFactorRepository")
 * @ORM\Table(name="tbl_medication_form_factor")
 * @Grid(
 *     api_admin_medication_form_factor_grid={
 *          {"id", "number", true, true, "mff.id"},
 *          {"title", "string", true, true, "mff.title"}
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
     * @Groups({"api_admin_medication_form_factor_list", "api_admin_medication_form_factor_get"})
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_medication_form_factor_add", "api_admin_medication_form_factor_edit"})
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_medication_form_factor_add", "api_admin_medication_form_factor_edit"}
     * )
     * @ORM\Column(name="title", type="string", length=200)
     * @Groups({"api_admin_medication_form_factor_grid", "api_admin_medication_form_factor_list", "api_admin_medication_form_factor_get"})
     */
    private $title;

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
}
