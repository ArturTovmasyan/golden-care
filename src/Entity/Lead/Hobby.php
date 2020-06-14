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
 * Class Hobby
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\HobbyRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_lead_hobby_add",
 *          "api_lead_hobby_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_lead_hobby")
 * @Grid(
 *     api_lead_hobby_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "h.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "h.title",
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
class Hobby
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_hobby_list",
     *     "api_lead_hobby_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_lead_hobby_add",
     *          "api_lead_hobby_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_hobby_add",
     *          "api_lead_hobby_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=120)
     * @Groups({
     *     "api_lead_hobby_list",
     *     "api_lead_hobby_get",
     *     "api_lead_lead_list",
     *     "api_lead_lead_get",
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_lead_hobby_add",
     *     "api_lead_hobby_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="leadHobbies")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_hobby_list",
     *     "api_lead_hobby_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Lead\Lead", mappedBy="hobbies", cascade={"persist"})
     */
    protected $leads;

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
     * @return mixed
     */
    public function getLeads()
    {
        return $this->leads;
    }

    /**
     * @param mixed $leads
     */
    public function setLeads($leads): void
    {
        $this->leads = $leads;

        /** @var Lead $lead */
        foreach ($this->leads as $lead) {
            $lead->addHobby($this);
        }
    }

    /**
     * @param Lead $lead
     */
    public function addLead(Lead $lead): void
    {
        $lead->addHobby($this);
        $this->leads[] = $lead;
    }

    /**
     * @param Lead $lead
     */
    public function removeLead(Lead $lead): void
    {
        $this->leads->removeElement($lead);
        $lead->removeHobby($this);
    }
}
