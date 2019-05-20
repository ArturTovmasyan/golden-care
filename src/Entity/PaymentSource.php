<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class PaymentSource
 *
 * @ORM\Entity(repositoryClass="App\Repository\PaymentSourceRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space.",
 *     groups={
 *          "api_admin_payment_source_add",
 *          "api_admin_payment_source_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_payment_source")
 * @Grid(
 *     api_admin_payment_source_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ps.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "ps.title",
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
class PaymentSource
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_payment_source_grid",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_payment_source_add",
     *     "api_admin_payment_source_edit"
     * })
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_payment_source_add",
     *          "api_admin_payment_source_edit"
     * })
     * @ORM\Column(name="title", type="string", length=50)
     * @Groups({
     *     "api_admin_payment_source_grid",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_payment_source_add",
     *     "api_admin_payment_source_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="paymentSources")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_payment_source_grid",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Lead", mappedBy="paymentType", cascade={"remove", "persist"})
     */
    private $leads;

    /**
     * @return int
     */
    public function getId()
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
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = preg_replace('/\s\s+/', ' ', $title);
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
    public function getLeads(): ArrayCollection
    {
        return $this->leads;
    }

    /**
     * @param ArrayCollection $leads
     */
    public function setLeads(ArrayCollection $leads): void
    {
        $this->leads = $leads;
    }
}
