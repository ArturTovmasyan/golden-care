<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Annotation\Grid;

/**
 * Class ReportLog
 *
 * @ORM\Entity(repositoryClass="App\Repository\ReportLogRepository")
 * @ORM\Table(name="tbl_report_activity_log")
 * @Grid(
 *     api_admin_report_log_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rl.id"
 *          },
 *          {
 *              "id"         = "created_at",
 *              "type"       = "date",
 *              "field"      = "rl.createdAt"
 *          },
 *          {
 *              "id"         = "created_by",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(u.firstName, ''), ' ', COALESCE(u.lastName, ''))",
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "rl.title"
 *          },
 *          {
 *              "id"         = "format",
 *              "type"       = "string_uppercase",
 *              "field"      = "rl.format"
 *          }
 *     }
 * )
 */
class ReportLog
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=512)
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(name="format", type="string", length=60)
     */
    private $format;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }
}
