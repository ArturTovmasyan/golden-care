<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class Report
 *
 * @ORM\Entity(repositoryClass="App\Repository\ReportRepository")
 * @ORM\Table(name="tbl_report")
 */
class Report
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
     * @ORM\Column(name="alias", type="string", length=100)
     */
    private $alias;

    /**
     * @var bool
     * @ORM\Column(name="pdf", type="boolean")
     */
    private $pdf;

    /**
     * @var bool
     * @ORM\Column(name="csv", type="boolean")
     */
    private $csv;

    /**
     * @return int
     */
    public function getId()
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
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     */
    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @return bool
     */
    public function isPdf(): bool
    {
        return $this->pdf;
    }

    /**
     * @param bool $pdf
     */
    public function setPdf(bool $pdf): void
    {
        $this->pdf = $pdf;
    }

    /**
     * @return bool
     */
    public function isCsv(): bool
    {
        return $this->csv;
    }

    /**
     * @param bool $csv
     */
    public function setCsv(bool $csv): void
    {
        $this->csv = $csv;
    }
}
