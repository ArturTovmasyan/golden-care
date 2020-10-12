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
 * Class ExpenseItem
 *
 * @ORM\Entity(repositoryClass="App\Repository\ExpenseItemRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_expense_item_add",
 *          "api_admin_expense_item_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_expense_item")
 * @Grid(
 *     api_admin_expense_item_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ex.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "ex.title",
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
class ExpenseItem
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_expense_item_list",
     *     "api_admin_expense_item_get",
     *     "api_admin_resident_expense_item_list",
     *     "api_admin_resident_expense_item_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_expense_item_add",
     *     "api_admin_expense_item_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_expense_item_add",
     *           "api_admin_expense_item_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_expense_item_list",
     *     "api_admin_expense_item_get",
     *     "api_admin_resident_expense_item_list",
     *     "api_admin_resident_expense_item_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_expense_item_add",
     *     "api_admin_expense_item_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="expenseItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_expense_item_list",
     *     "api_admin_expense_item_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentExpenseItem", mappedBy="expenseItem", cascade={"remove", "persist"})
     */
    private $residentExpenseItems;

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
    public function getResidentExpenseItems(): ArrayCollection
    {
        return $this->residentExpenseItems;
    }

    /**
     * @param ArrayCollection $residentExpenseItems
     */
    public function setResidentExpenseItems(ArrayCollection $residentExpenseItems): void
    {
        $this->residentExpenseItems = $residentExpenseItems;
    }
}
