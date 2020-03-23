<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * ReportCsvView
 *
 * @ORM\Table(name="tbl_report_csv_view")
 * @ORM\Entity()
 */
class ReportCsvView
{
    use TimeAwareTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=255)
     */
    private $hash = '';

    /**
     * @var int
     *
     * @ORM\Column(name="expires_at", type="integer")
     */
    private $expiresAt;

    /**
     * @var array $params
     * @ORM\Column(name="params", type="json_array", nullable=true)
     */
    private $params = [];

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    /**
     * @return int
     */
    public function getExpiresAt(): int
    {
        return $this->expiresAt;
    }

    /**
     * @param int $expiresAt
     */
    public function setExpiresAt(int $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }
}

