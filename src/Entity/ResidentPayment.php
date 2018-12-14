<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentPayment
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentPaymentRepository")
 * @ORM\Table(name="tbl_resident_payment")
 * @Grid(
 *     api_admin_resident_payment_grid={
 *          {"id", "number", true, true, "rp.id"},
 *          {"date", "string", true, true, "rp.date"},
 *          {"amount", "number", true, true, "rp.amount"},
 *          {"notes", "string", true, true, "rp.notes"},
 *          {"source", "string", true, true, "rp.source"},
 *     }
 * )
 */
class ResidentPayment
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_payment_grid",
     *     "api_admin_resident_payment_list",
     *     "api_admin_resident_payment_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={"api_admin_resident_payment_add", "api_admin_resident_payment_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({"api_admin_resident_payment_grid", "api_admin_resident_payment_list", "api_admin_resident_payment_get"})
     */
    private $resident;

    /**
     * @var \DateTime
     * @Assert\NotBlank(groups={"api_admin_resident_payment_add", "api_admin_resident_payment_edit"})
     * @Assert\DateTime(groups={"api_admin_resident_payment_add", "api_admin_resident_payment_edit"})
     * @ORM\Column(name="date", type="datetime")
     * @Groups({"api_admin_resident_payment_grid", "api_admin_resident_payment_list", "api_admin_resident_payment_get"})
     */
    private $date;

    /**
     * @var int $amount
     * @ORM\Column(name="amount", type="float", length=10)
     * @Assert\NotBlank(groups={"api_admin_resident_payment_add", "api_admin_resident_payment_edit"})
     * @Assert\Regex(
     *      pattern="/(^0$)|(^[1-9][0-9]*$)|(^[0-9]+(\.[0-9]{1,2})$)/",
     *      message="The value {{ value }} is not a valid type. Try to add something like '2000, 0.55, 100.34'",
     *      groups={"api_admin_resident_payment_add", "api_admin_resident_payment_edit"}
     * )
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "Amount cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_payment_add", "api_admin_resident_payment_edit"}
     * )
     * @Groups({"api_admin_resident_payment_grid", "api_admin_resident_payment_list", "api_admin_resident_payment_get"})
     */
    private $amount;

    /**
     * @var string $notes
     * @ORM\Column(name="notes", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Notes cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_resident_payment_add", "api_admin_resident_payment_edit"}
     * )
     * @Groups({"api_admin_resident_payment_grid", "api_admin_resident_payment_list", "api_admin_resident_payment_get"})
     */
    private $notes;

    /**
     * @var array $source
     * @ORM\Column(name="source", type="json_array", length=1024, nullable=true)
     * @Assert\Count(
     *      max = 10,
     *      maxMessage = "You cannot specify more than {{ limit }} sources",
     *      groups={"api_admin_resident_payment_add", "api_admin_resident_payment_edit"}
     * )
     * @Groups({"api_admin_resident_payment_grid", "api_admin_resident_payment_list", "api_admin_resident_payment_get"})
     */
    private $source = [];

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
     * @return ResidentPayment
     */
    public function setResident(?Resident $resident): self
    {
        $this->resident = $resident;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
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

    /**
     * @return array
     */
    public function getSource(): array
    {
        return $this->source;
    }

    /**
     * @param array $source
     */
    public function setSource(array $source): void
    {
        $this->source = $source;
    }
}
