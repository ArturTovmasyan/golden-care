<?php

namespace App\Model\Persistence\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

trait LogAwareTrait
{
    /**
     * @var string
     * @ORM\Column(type="string", length=5000, nullable=true)
     */
    private $message;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $level;

    /**
     * @var \Datetime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level): void
    {
        $this->level = $level;
    }

    /**
     * @return \Datetime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \Datetime $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}