<?php
namespace App\Entity;

use App\Model\Persistence\Entity\PhoneTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Annotation\Grid as Grid;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_responsible_person_phone")
 * @ORM\Entity(repositoryClass="App\Repository\ResponsiblePersonPhoneRepository")
 */
class ResponsiblePersonPhone
{
    use PhoneTrait;

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups({
     *      "api_admin_responsible_person_list",
     *      "api_admin_responsible_person_get",
     *      "api_admin_resident_responsible_person_list"
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="ResponsiblePerson", inversedBy="phones", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_responsible_person", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_responsible_person_add",
     *     "api_admin_responsible_person_edit"
     * })
     */
    private $responsiblePerson;

    /**
     * @ORM\Column(name="extension", type="integer", nullable=true)
     * @Groups({
     *      "api_admin_responsible_person_list",
     *      "api_admin_responsible_person_get",
     *      "api_admin_resident_responsible_person_list"
     * })
     */
    private $extension;

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
     * @return mixed
     */
    public function getResponsiblePerson()
    {
        return $this->responsiblePerson;
    }

    /**
     * @param mixed $responsiblePerson
     */
    public function setResponsiblePerson($responsiblePerson): void
    {
        $this->responsiblePerson = $responsiblePerson;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     */
    public function setExtension($extension): void
    {
        $this->extension = $extension;
    }
}
