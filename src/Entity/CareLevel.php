<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class CareLevel
 *
 * @ORM\Entity(repositoryClass="App\Repository\CareLevelRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_care_level_add",
 *          "api_admin_care_level_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_care_level")
 * @Grid(
 *     api_admin_care_level_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "cl.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "cl.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(cl.description, 1, 100)), CASE WHEN LENGTH(cl.description) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class CareLevel
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_care_level_grid",
     *     "api_admin_care_level_list",
     *     "api_admin_care_level_get",
     *     "api_admin_resident_grid",
     *     "api_admin_resident_list",
     *     "api_admin_resident_get",
     *     "api_admin_resident_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active",
     *     "api_admin_resident_get_last_admission",
     *     "api_admin_facility_room_type_list",
     *     "api_admin_facility_room_type_get",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_care_level_add",
     *     "api_admin_care_level_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_care_level_add",
     *           "api_admin_care_level_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_care_level_grid",
     *     "api_admin_care_level_list",
     *     "api_admin_care_level_get",
     *     "api_admin_resident_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_resident_admission_get_active",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active",
     *     "api_admin_resident_get_last_admission",
     *     "api_admin_facility_room_type_list",
     *     "api_admin_facility_room_type_get",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $title;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=500, nullable=true)
     * @Assert\Length(
     *      max = 500,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_care_level_add",
     *          "api_admin_care_level_edit"
     * })
     * @Groups({
     *     "api_admin_care_level_grid",
     *     "api_admin_care_level_list",
     *     "api_admin_care_level_get"
     * })
     */
    private $description;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_care_level_add",
     *     "api_admin_care_level_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="careLevels")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_care_level_grid",
     *     "api_admin_care_level_list",
     *     "api_admin_care_level_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentAdmission", mappedBy="careLevel", cascade={"remove", "persist"})
     */
    private $residentAdmissions;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\BaseRate", mappedBy="careLevel", cascade={"remove", "persist"})
     */
    private $baseRates;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\SourceBaseRate", mappedBy="careLevel", cascade={"remove", "persist"})
     */
    private $sourceBaseRates;

    public function getId(): ?int
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
    public function getResidentAdmissions(): ArrayCollection
    {
        return $this->residentAdmissions;
    }

    /**
     * @param ArrayCollection $residentAdmissions
     */
    public function setResidentAdmissions(ArrayCollection $residentAdmissions): void
    {
        $this->residentAdmissions = $residentAdmissions;
    }

    /**
     * @return ArrayCollection
     */
    public function getBaseRates(): ArrayCollection
    {
        return $this->baseRates;
    }

    /**
     * @param ArrayCollection $baseRates
     */
    public function setBaseRates(ArrayCollection $baseRates): void
    {
        $this->baseRates = $baseRates;
    }

    /**
     * @return ArrayCollection
     */
    public function getSourceBaseRates(): ArrayCollection
    {
        return $this->sourceBaseRates;
    }

    /**
     * @param ArrayCollection $sourceBaseRates
     */
    public function setSourceBaseRates(ArrayCollection $sourceBaseRates): void
    {
        $this->sourceBaseRates = $sourceBaseRates;
    }
}
