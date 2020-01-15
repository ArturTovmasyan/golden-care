<?php

namespace App\Entity\Lead;

use App\Model\Persistence\Entity\PhoneTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_lead_contact_phone")
 * @ORM\Entity(repositoryClass="App\Repository\Lead\ContactPhoneRepository")
 */
class ContactPhone
{
    use PhoneTrait;

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups({
     *      "api_lead_contact_list",
     *      "api_lead_contact_get",
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Contact", inversedBy="phones", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_contact", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *      "api_lead_contact_add",
     *      "api_lead_contact_edit"
     * })
     */
    private $contact;

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
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param $contact
     */
    public function setContact($contact): void
    {
        $this->contact = $contact;
    }
}
