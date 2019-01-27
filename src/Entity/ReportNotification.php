<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ReportNotification
 *
 * @ORM\Entity(repositoryClass="App\Repository\ReportNotificationRepository")
 * @ORM\Table(name="tbl_report_notification")
 * @Grid(
 *     api_admin_report_notification_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rn.id"
 *          },
 *          {
 *              "id"         = "subject",
 *              "type"       = "string",
 *              "field"      = "rn.subject"
 *          },
 *          {
 *              "id"         = "email_to",
 *              "type"       = "string",
 *              "field"      = "rn.email_to"
 *          },
 *          {
 *              "id"         = "email_cc",
 *              "type"       = "string",
 *              "field"      = "rn.email_cc"
 *          },
 *          {
 *              "id"         = "alias",
 *              "type"       = "string",
 *              "field"      = "rn.alias"
 *          }
 *     }
 * )
 */
class ReportNotification
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
     * @ORM\Column(name="alias", type="string", length=255, nullable=false)
     */
    private $alias;

    /**
     * @var array
     * @ORM\Column(name="parameters", type="json_array", nullable=false)
     */
    private $parameters = [];

    /**
     * @var string
     * @ORM\Column(name="email_to", type="text", nullable=false)
     */
    private $email_to;

    /**
     * @var string
     * @ORM\Column(name="email_cc", type="text", nullable=false)
     */
    private $email_cc;

    /**
     * @var string
     * @ORM\Column(name="subject", type="string", length=255, nullable=false)
     */
    private $subject;

    /**
     * @var string
     * @ORM\Column(name="body", type="text", nullable=false)
     */
    private $body;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled;

    /**
     * @var array
     * @ORM\Column(name="dows", type="json_array", nullable=false)
     * @Assert\All({
     *     @Assert\Choice(choices={
     *          0, 1, 2, 3, 4, 5, 6
     *     })
     * })
     */
    private $dows = [];

    /**
     * @var array
     * @ORM\Column(name="doms", type="json_array", nullable=false)
     * @Assert\All({
     *     @Assert\Choice(choices={
     *           0,  1,  2,  3,  4,  5,  6,  7,  8,  9,
     *          10, 11, 12, 13, 14, 15, 16, 17, 18, 19,
     *          20, 21, 22, 23, 24, 25, 26, 27, 28, 29
     *     })
     * })
     */
    private $doms = [];

    /**
     * @var \DateTime
     * @ORM\Column(type="time", nullable=false)
     */
    private $time;

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
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias(?string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @return array
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(?array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getEmailTo(): ?string
    {
        return $this->email_to;
    }

    /**
     * @param string $email_to
     */
    public function setEmailTo(?string $email_to): void
    {
        $this->email_to = $email_to;
    }

    /**
     * @return string
     */
    public function getEmailCc(): ?string
    {
        return $this->email_cc;
    }

    /**
     * @param string $email_cc
     */
    public function setEmailCc(?string $email_cc): void
    {
        $this->email_cc = $email_cc;
    }

    /**
     * @return string
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return bool
     */
    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(?bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return array
     */
    public function getDows(): ?array
    {
        return $this->dows;
    }

    /**
     * @param array $dows
     */
    public function setDows(?array $dows): void
    {
        $this->dows = $dows;
    }

    /**
     * @return array
     */
    public function getDoms(): ?array
    {
        return $this->doms;
    }

    /**
     * @param array $doms
     */
    public function setDoms(?array $doms): void
    {
        $this->doms = $doms;
    }

    /**
     * @return \DateTime
     */
    public function getTime(): ?\DateTime
    {
        return $this->time;
    }

    /**
     * @param \DateTime $time
     */
    public function setTime(?\DateTime $time): void
    {
        $this->time = $time;
    }
}
