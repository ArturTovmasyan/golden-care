<?php

namespace App\Entity\Lead;

use App\Entity\Space;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class StateChangeReason
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\StateChangeReasonRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space.",
 *     groups={
 *          "api_lead_state_change_reason_add",
 *          "api_lead_state_change_reason_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_lead_state_change_reason")
 * @Grid(
 *     api_lead_state_change_reason_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "scr.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "scr.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "state",
 *              "type"       = "enum",
 *              "field"      = "scr.state",
 *              "values"     = "\App\Model\Lead\State::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class StateChangeReason
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_state_change_reason_list",
     *     "api_lead_state_change_reason_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_lead_state_change_reason_add",
     *          "api_lead_state_change_reason_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_state_change_reason_add",
     *          "api_lead_state_change_reason_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=120)
     * @Groups({
     *     "api_lead_state_change_reason_list",
     *     "api_lead_state_change_reason_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get"
     * })
     */
    private $title;

    /**
     * @var int
     * @ORM\Column(name="state", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\Lead\State","getTypeValues"},
     *     groups={
     *          "api_lead_state_change_reason_add",
     *          "api_lead_state_change_reason_edit"
     *     }
     * )
     * @Groups({
     *     "api_lead_state_change_reason_grid",
     *     "api_lead_state_change_reason_list",
     *     "api_lead_state_change_reason_get"
     * })
     */
    private $state;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_lead_state_change_reason_add",
     *     "api_lead_state_change_reason_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="leadStateChangeReasons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_state_change_reason_grid",
     *     "api_lead_state_change_reason_list",
     *     "api_lead_state_change_reason_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Lead", mappedBy="stateChangeReason", cascade={"remove", "persist"})
     */
    private $leads;

    public function getId()
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
     * @return int|null
     */
    public function getState(): ?int
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
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
