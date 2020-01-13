<?php

namespace App\Entity\Lead;

use App\Model\Persistence\Entity\PhoneTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_lead_organization_phone")
 * @ORM\Entity(repositoryClass="App\Repository\Lead\OrganizationPhoneRepository")
 */
class OrganizationPhone
{
    use PhoneTrait;

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups({
     *      "api_lead_organization_list",
     *      "api_lead_organization_get"
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Organization", inversedBy="phones", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_organization", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_lead_organization_add",
     *     "api_lead_organization_edit"
     * })
     */
    private $organization;

    /**
     * @return int
     */
    public function getId(): ?int
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
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param $organization
     */
    public function setOrganization($organization): void
    {
        $this->organization = $organization;
    }
}
