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
 * Class LatePayment
 *
 * @ORM\Entity(repositoryClass="App\Repository\LatePaymentRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_late_payment_add",
 *          "api_admin_late_payment_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_late_payment")
 * @Grid(
 *     api_admin_late_payment_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "lp.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "lp.title",
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
class LatePayment
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_late_payment_list",
     *     "api_admin_late_payment_get",
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_late_payment_add",
     *     "api_admin_late_payment_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_late_payment_add",
     *           "api_admin_late_payment_edit"
     * })
     * @ORM\Column(name="title", type="string", length=60)
     * @Groups({
     *     "api_admin_late_payment_list",
     *     "api_admin_late_payment_get",
     *     "api_admin_resident_ledger_list",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_late_payment_add",
     *     "api_admin_late_payment_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="expenseItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_late_payment_list",
     *     "api_admin_late_payment_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentLedger", mappedBy="latePayment", cascade={"persist"})
     */
    private $residentLedgers;

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
    public function getResidentLedgers(): ArrayCollection
    {
        return $this->residentLedgers;
    }

    /**
     * @param ArrayCollection $residentLedgers
     */
    public function setResidentLedgers(ArrayCollection $residentLedgers): void
    {
        $this->residentLedgers = $residentLedgers;
    }
}
