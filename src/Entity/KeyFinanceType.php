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
 * Class KeyFinanceType
 *
 * @ORM\Entity(repositoryClass="App\Repository\KeyFinanceTypeRepository")
 * @UniqueEntity(
 *     fields={"space", "type"},
 *     errorPath="type",
 *     message="The Type is already in use in this space.",
 *     groups={
 *          "api_admin_key_finance_type_add",
 *          "api_admin_key_finance_type_edit"
 *     }
 * )
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_key_finance_type_add",
 *          "api_admin_key_finance_type_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_key_finance_type")
 * @Grid(
 *     api_admin_key_finance_type_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "kft.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "kft.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(kft.description, 1, 100)), CASE WHEN LENGTH(kft.description) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class KeyFinanceType
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_key_finance_type_list",
     *     "api_admin_key_finance_type_get",
     *     "api_admin_resident_key_finance_date_list",
     *     "api_admin_resident_key_finance_date_get"
     * })
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="type", type="smallint")
     * @Assert\Choice(
     *     callback={"\App\Model\KeyFinanceType","getTypeValues"},
     *     groups={
     *          "api_admin_key_finance_type_add",
     *          "api_admin_key_finance_type_edit"
     *     }
     * )
     * @Groups({
     *     "api_admin_key_finance_type_list",
     *     "api_admin_key_finance_type_get",
     *     "api_admin_resident_key_finance_date_list",
     *     "api_admin_resident_key_finance_date_get"
     * })
     */
    private $type;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_key_finance_type_add",
     *     "api_admin_key_finance_type_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_key_finance_type_add",
     *           "api_admin_key_finance_type_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_key_finance_type_list",
     *     "api_admin_key_finance_type_get",
     *     "api_admin_resident_key_finance_date_list",
     *     "api_admin_resident_key_finance_date_get"
     * })
     */
    private $title;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=256, nullable=true)
     * @Assert\Length(
     *      max = 256,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_key_finance_type_add",
     *          "api_admin_key_finance_type_edit"
     * })
     * @Groups({
     *     "api_admin_key_finance_type_list",
     *     "api_admin_key_finance_type_get",
     *     "api_admin_resident_key_finance_date_list",
     *     "api_admin_resident_key_finance_date_get"
     * })
     */
    private $description;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_key_finance_type_add",
     *     "api_admin_key_finance_type_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="keyFinanceTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_key_finance_type_list",
     *     "api_admin_key_finance_type_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentKeyFinanceDate", mappedBy="keyFinanceType", cascade={"remove", "persist"})
     */
    private $residentKeyFinanceDates;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int|null $type
     */
    public function setType(?int $type): void
    {
        $this->type = $type;
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
    public function getResidentKeyFinanceDates(): ArrayCollection
    {
        return $this->residentKeyFinanceDates;
    }

    /**
     * @param ArrayCollection $residentKeyFinanceDates
     */
    public function setResidentKeyFinanceDates(ArrayCollection $residentKeyFinanceDates): void
    {
        $this->residentKeyFinanceDates = $residentKeyFinanceDates;
    }
}
