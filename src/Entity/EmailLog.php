<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Annotation\Grid;

/**
 * Class EmailLog
 *
 * @ORM\Entity(repositoryClass="App\Repository\EmailLogRepository")
 * @ORM\Table(name="tbl_email_log")
 * @Grid(
 *     api_admin_email_log_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "el.id"
 *          },
 *          {
 *              "id"         = "date",
 *              "type"       = "date",
 *              "field"      = "el.createdAt"
 *          },
 *          {
 *              "id"         = "success",
 *              "type"       = "boolean",
 *              "field"      = "el.success"
 *          },
 *          {
 *              "id"         = "subject",
 *              "type"       = "string",
 *              "field"      = "el.subject"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "el.space"
 *          },
 *          {
 *              "id"         = "emails",
 *              "type"       = "string",
 *              "field"      = "el.emails"
 *          }
 *     }
 * )
 */
class EmailLog
{
    use TimeAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     * @ORM\Column(name="success", type="boolean")
     */
    private $success;

    /**
     * @var string
     * @ORM\Column(name="subject", type="string", length=512)
     */
    private $subject;

    /**
     * @var string
     * @ORM\Column(name="space", type="string", length=128)
     */
    private $space;

    /**
     * @var array $emails
     * @ORM\Column(name="emails", type="json_array", nullable=true)
     */
    private $emails = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    /**
     * @return null|string
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param null|string $subject
     */
    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return null|string
     */
    public function getSpace(): ?string
    {
        return $this->space;
    }

    /**
     * @param null|string $space
     */
    public function setSpace(?string $space): void
    {
        $this->space = $space;
    }

    /**
     * @return array
     */
    public function getEmails(): array
    {
        return $this->emails;
    }

    /**
     * @param array $emails
     */
    public function setEmails(array $emails): void
    {
        $this->emails = $emails;
    }
}
