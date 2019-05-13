<?php

namespace App\Entity\Lead;

use App\Entity\Space;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ActivityStatus
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\ActivityStatusRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space.",
 *     groups={
 *          "api_lead_activity_status_add",
 *          "api_lead_activity_status_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_lead_activity_status")
 * @Grid(
 *     api_lead_activity_status_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "ast.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "ast.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "done",
 *              "type"       = "boolean",
 *              "field"      = "ast.done"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class ActivityStatus
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_activity_status_list",
     *     "api_lead_activity_status_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_lead_activity_status_add",
     *          "api_lead_activity_status_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_activity_status_add",
     *          "api_lead_activity_status_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=60)
     * @Groups({
     *     "api_lead_activity_status_grid",
     *     "api_lead_activity_status_list",
     *     "api_lead_activity_status_get"
     * })
     */
    private $title;

    /**
     * @var bool
     * @ORM\Column(name="done", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_lead_activity_status_grid",
     *     "api_lead_activity_status_list",
     *     "api_lead_activity_status_get"
     * })
     */
    protected $done;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_lead_activity_status_add",
     *     "api_lead_activity_status_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="leadActivityStatuses")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_activity_status_grid",
     *     "api_lead_activity_status_list",
     *     "api_lead_activity_status_get"
     * })
     */
    private $space;

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
     * @return bool
     */
    public function isDone(): bool
    {
        return $this->done;
    }

    /**
     * @param bool $done
     */
    public function setDone(bool $done): void
    {
        $this->done = $done;
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
