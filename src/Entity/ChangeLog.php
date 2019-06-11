<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ChangeLog
 *
 * @ORM\Entity(repositoryClass="App\Repository\ChangeLogRepository")
 * @ORM\Table(name="tbl_change_log")
 * @Grid(
 *     api_admin_change_log_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "cl.id"
 *          },
 *          {
 *              "id"         = "type",
 *              "type"       = "enum",
 *              "field"      = "cl.type",
 *              "values"     = "\App\Model\ChangeLogType::getTypeDefaultNames"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "cl.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "content",
 *              "type"       = "string",
 *              "field"      = "cl.content",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "owner",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(o.firstName, ''), ' ', COALESCE(o.lastName, ''))"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class ChangeLog
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_change_log_list",
     *     "api_admin_change_log_get"
     * })
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="type", type="smallint")
     * @Assert\Choice(
     *     callback={"App\Model\ChangeLogType","getTypeValues"},
     *     groups={
     *          "api_admin_change_log_add",
     *          "api_admin_change_log_edit"
     *     }
     * )
     * @Groups({
     *     "api_admin_change_log_list",
     *     "api_admin_change_log_get"
     * })
     */
    private $type;

    /**
     * @var string
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_change_log_add",
     *          "api_admin_change_log_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=512, nullable=true)
     * @Groups({
     *     "api_admin_change_log_list",
     *     "api_admin_change_log_get"
     * })
     */
    private $title;

    /**
     * @var string $content
     * @ORM\Column(name="content", type="text", nullable=true)
     * @Groups({
     *     "api_admin_change_log_list",
     *     "api_admin_change_log_get"
     * })
     */
    private $content;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="changeLogs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_owner", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_change_log_list",
     *     "api_admin_change_log_get"
     * })
     */
    private $owner;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_change_log_add",
     *     "api_admin_change_log_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="changeLogs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_change_log_list",
     *     "api_admin_change_log_get"
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
        $this->title = $title;
    }

    /**
     * @return int|null
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * @param int|null $type
     */
    public function setType(?int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return null|string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param null|string $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return User|null
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User|null $owner
     */
    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
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
