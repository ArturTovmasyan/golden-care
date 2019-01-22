<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class PaymentSource
 *
 * @ORM\Entity(repositoryClass="App\Repository\PaymentSourceRepository")
 * @ORM\Table(name="tbl_payment_source")
 * @Grid(
 *     api_admin_payment_source_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "sortable"   = true,
 *              "filterable" = true,
 *              "field"      = "ps.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "sortable"   = true,
 *              "filterable" = true,
 *              "field"      = "ps.title"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "sortable"   = true,
 *              "filterable" = true,
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
     *     "api_admin_payment_source_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_payment_source_add", "api_admin_payment_source_edit"})
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_payment_source_add", "api_admin_payment_source_edit"}
     * )
     * @ORM\Column(name="title", type="string", length=50)
     * @Groups({
     *     "api_admin_payment_source_grid",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={"api_admin_payment_source_add", "api_admin_payment_source_edit"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Space")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_admin_payment_source_grid",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $space;

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
     * @return PaymentSource
     */
    public function setSpace(?Space $space): self
    {
        $this->space = $space;

        return $this;
    }
}
