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
 * @UniqueEntity(fields="name", message="Sorry, this name is already in use.", groups={"api_admin_medication_add", "api_admin_medication_edit"})
 * @Grid(
 *     api_admin_medication_list={
 *          {"id", "number", true, true, "m.id"},
 *          {"name", "string", true, true, "m.name"}
 *     },
 *     api_dashboard_medication_list={
 *          {"id", "number", true, true, "m.id"},
 *          {"name", "string", true, true, "m.name"}
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
     * @Groups({"api_admin_medication_list", "api_admin_medication_get", "api_dashboard_medication_list"})
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=20, nullable=false)
     * @Groups({"api_admin_medication_list", "api_admin_medication_get", "api_dashboard_medication_list"})
     * @Assert\NotBlank(groups={"api_admin_medication_add", "api_admin_medication_edit"})
     */
    private $name;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
