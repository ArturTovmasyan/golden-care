<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class CreditDiscountItem
 *
 * @ORM\Entity(repositoryClass="App\Repository\CreditDiscountItemRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_credit_discount_item_add",
 *          "api_admin_credit_discount_item_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_credit_discount_item")
 * @Grid(
 *     api_admin_credit_discount_item_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "cdi.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "cdi.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "amount",
 *              "type"       = "number",
 *              "field"      = "cdi.amount"
 *          },
 *          {
 *              "id"         = "can_be_changed",
 *              "type"       = "boolean",
 *              "field"      = "cdi.canBeChanged"
 *          },
 *          {
 *              "id"         = "valid_through_date",
 *              "type"       = "date",
 *              "field"      = "cdi.validThroughDate"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class CreditDiscountItem
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_credit_discount_item_list",
     *     "api_admin_credit_discount_item_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_credit_discount_item_add",
     *     "api_admin_credit_discount_item_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_credit_discount_item_add",
     *           "api_admin_credit_discount_item_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_credit_discount_item_list",
     *     "api_admin_credit_discount_item_get"
     * })
     */
    private $title;

    /**
     * @var float
     * @ORM\Column(name="amount", type="float", length=10, nullable=true)
     * @Assert\GreaterThan(
     *      value = 0,
     *      message = "This value should be greater than ${{ value }}.",
     *      groups={
     *          "api_admin_credit_discount_item_edit",
     *          "api_admin_credit_discount_item_add"
     *      }
     * )
     * @Assert\LessThan(
     *      value = 1000000,
     *      message = "This value should be less than ${{ value }}.",
     *      groups={
     *          "api_admin_credit_discount_item_edit",
     *          "api_admin_credit_discount_item_add"
     *      }
     * )
     * @Groups({
     *     "api_admin_credit_discount_item_get",
     *     "api_admin_credit_discount_item_list"
     * })
     */
    private $amount;

    /**
     * @var bool
     * @ORM\Column(name="can_be_changed", type="boolean")
     * @Groups({
     *     "api_admin_credit_discount_item_get",
     *     "api_admin_credit_discount_item_list"
     * })
     */
    private $canBeChanged;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={
     *     "api_admin_credit_discount_item_add",
     *     "api_admin_credit_discount_item_edit"
     * })
     * @Assert\DateTime(groups={
     *     "api_admin_credit_discount_item_add",
     *     "api_admin_credit_discount_item_edit"
     * })
     * @ORM\Column(name="valid_through_date", type="datetime")
     * @Groups({
     *     "api_admin_credit_discount_item_get",
     *     "api_admin_credit_discount_item_list"
     * })
     */
    private $validThroughDate;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_credit_discount_item_add",
     *     "api_admin_credit_discount_item_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="creditDiscountItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_credit_discount_item_list",
     *     "api_admin_credit_discount_item_get"
     * })
     */
    private $space;

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

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float|null $amount
     */
    public function setAmount(?float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return bool
     */
    public function isCanBeChanged(): bool
    {
        return $this->canBeChanged;
    }

    /**
     * @param bool $canBeChanged
     */
    public function setCanBeChanged(bool $canBeChanged): void
    {
        $this->canBeChanged = $canBeChanged;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidThroughDate(): ?\DateTime
    {
        return $this->validThroughDate;
    }

    /**
     * @param \DateTime|null $validThroughDate
     */
    public function setValidThroughDate(?\DateTime $validThroughDate): void
    {
        $this->validThroughDate = $validThroughDate;
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
}
