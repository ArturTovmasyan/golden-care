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
 * Class RpPaymentType
 *
 * @ORM\Entity(repositoryClass="App\Repository\RpPaymentTypeRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_rp_payment_type_add",
 *          "api_admin_rp_payment_type_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_rp_payment_type")
 * @Grid(
 *     api_admin_rp_payment_type_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "pt.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "pt.title",
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
class RpPaymentType
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_rp_payment_type_list",
     *     "api_admin_rp_payment_type_get",
     *     "api_admin_resident_payment_received_item_list",
     *     "api_admin_resident_payment_received_item_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_rp_payment_type_add",
     *     "api_admin_rp_payment_type_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_rp_payment_type_add",
     *           "api_admin_rp_payment_type_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_rp_payment_type_list",
     *     "api_admin_rp_payment_type_get",
     *     "api_admin_resident_payment_received_item_list",
     *     "api_admin_resident_payment_received_item_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_rp_payment_type_add",
     *     "api_admin_rp_payment_type_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="rpPaymentTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_rp_payment_type_list",
     *     "api_admin_rp_payment_type_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentPaymentReceivedItem", mappedBy="paymentType", cascade={"remove", "persist"})
     */
    private $residentPaymentReceivedItems;

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
    public function getResidentPaymentReceivedItems(): ArrayCollection
    {
        return $this->residentPaymentReceivedItems;
    }

    /**
     * @param ArrayCollection $residentPaymentReceivedItems
     */
    public function setResidentPaymentReceivedItems(ArrayCollection $residentPaymentReceivedItems): void
    {
        $this->residentPaymentReceivedItems = $residentPaymentReceivedItems;
    }
}
