<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * @ORM\Table(name="tbl_hospice_provider")
 * @ORM\Entity(repositoryClass="App\Repository\HospiceProviderRepository")
 * @UniqueEntity(
 *     fields={"space", "name"},
 *     errorPath="name",
 *     message="The name is already in use in this space.",
 *     groups={
 *          "api_admin_hospice_provider_add",
 *          "api_admin_hospice_provider_edit"
 *     }
 * )
 * @Grid(
 *     api_admin_hospice_provider_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "hp.id"
 *          },
 *          {
 *              "id"         = "name",
 *              "type"       = "string",
 *              "field"      = "hp.name"
 *          },
 *          {
 *              "id"         = "address_1",
 *              "type"       = "string",
 *              "field"      = "hp.address_1"
 *          },
 *          {
 *              "id"         = "address_2",
 *              "type"       = "string",
 *              "field"      = "hp.address_2"
 *          },
 *          {
 *              "id"         = "csz_str",
 *              "type"       = "string",
 *              "field"      = "CONCAT(csz.city, ' ', csz.stateAbbr, ', ', csz.zipMain)"
 *          },
 *          {
 *              "id"         = "phone",
 *              "type"       = "string",
 *              "field"      = "hp.phone"
 *          },
 *          {
 *              "id"         = "email",
 *              "type"       = "string",
 *              "field"      = "hp.email"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class HospiceProvider
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_hospice_provider_list",
     *     "api_admin_hospice_provider_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=60)
     * @Assert\NotBlank(groups={
     *     "api_admin_hospice_provider_add",
     *     "api_admin_hospice_provider_edit"
     * })
     * @Assert\Length(
     *      max = 60,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_hospice_provider_add",
     *          "api_admin_hospice_provider_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_hospice_provider_list",
     *     "api_admin_hospice_provider_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get"
     * })
     */
    private $name;

    /**
     * @var string
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Address cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_hospice_provider_add",
     *          "api_admin_hospice_provider_edit"
     *      }
     * )
     * @ORM\Column(name="address_1", type="string", length=100)
     * @Groups({
     *     "api_admin_hospice_provider_list",
     *     "api_admin_hospice_provider_get"
     * })
     */
    private $address_1;

    /**
     * @var string
     *
     * @ORM\Column(name="address_2", type="string", length=100, nullable=true)
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "Address (optional) cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_hospice_provider_add",
     *          "api_admin_hospice_provider_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_hospice_provider_list",
     *     "api_admin_hospice_provider_get"
     * })
     */
    private $address_2;

    /**
     * @var CityStateZip
     * @ORM\ManyToOne(targetEntity="CityStateZip", inversedBy="hospiceProviders")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Groups({
     *     "api_admin_hospice_provider_list",
     *     "api_admin_hospice_provider_get"
     * })
     */
    private $csz;

    /**
     * @var string
     * @Assert\Regex(
     *     pattern="/^\([0-9]{3}\)\s?[0-9]{3}-[0-9]{4}$/",
     *     message="Invalid phone number format. Valid format is (XXX) XXX-XXXX.",
     *     groups={
     *          "api_admin_hospice_provider_add",
     *          "api_admin_hospice_provider_edit"
     * })
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     * @Groups({
     *     "api_admin_hospice_provider_list",
     *     "api_admin_hospice_provider_get"
     * })
     */
    private $phone;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Assert\Email(
     *     groups={
     *          "api_admin_hospice_provider_add",
     *          "api_admin_hospice_provider_edit"
     *     }
     * )
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Email cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_hospice_provider_add",
     *          "api_admin_hospice_provider_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_hospice_provider_list",
     *     "api_admin_hospice_provider_get"
     * })
     */
    private $email;

    /**
     * @var Space
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="hospiceProviders")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *     message = "Please select a Space",
     *     groups={
     *          "api_admin_hospice_provider_add",
     *          "api_admin_hospice_provider_edit"
     *     }
     * )
     * @Groups({
     *     "api_admin_hospice_provider_list",
     *     "api_admin_hospice_provider_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentEvent", mappedBy="hospiceProvider", cascade={"remove", "persist"})
     */
    private $residentEvents;

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
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName(?string $name): void
    {
        $name = preg_replace('/\s\s+/', ' ', $name);
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getAddress1(): ?string
    {
        return $this->address_1;
    }

    /**
     * @param null|string $address1
     */
    public function setAddress1(?string $address1): void
    {
        $this->address_1 = $address1;
    }

    /**
     * @return null|string
     */
    public function getAddress2(): ?string
    {
        return $this->address_2;
    }

    /**
     * @param null|string $address2
     */
    public function setAddress2(?string $address2): void
    {
        $this->address_2 = $address2;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return null|string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param null|string $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return CityStateZip|null
     */
    public function getCsz(): ?CityStateZip
    {
        return $this->csz;
    }

    /**
     * @param CityStateZip|null $csz
     */
    public function setCsz(?CityStateZip $csz): void
    {
        $this->csz = $csz;
    }

    /**
     * @return Space
     */
    public function getSpace(): Space
    {
        return $this->space;
    }

    /**
     * @param Space $space
     */
    public function setSpace(Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentEvents(): ArrayCollection
    {
        return $this->residentEvents;
    }

    /**
     * @param ArrayCollection $residentEvents
     */
    public function setResidentEvents(ArrayCollection $residentEvents): void
    {
        $this->residentEvents = $residentEvents;
    }
}
