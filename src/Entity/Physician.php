<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Annotation\Grid as Grid;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_physician")
 * @ORM\Entity(repositoryClass="App\Repository\PhysicianRepository")
 * @Grid(
 *     api_admin_physician_grid={
 *          {"id", "number", true, true, "p.id"},
 *          {"first_name", "string", true, true, "p.firstName"},
 *          {"middle_name", "string", true, true, "p.middleName"},
 *          {"last_name", "string", true, true, "p.lastName"},
 *          {"address_1", "string", true, true, "p.address_1"},
 *          {"address_2", "string", true, true, "p.address_2"},
 *          {"office_phone", "string", true, true, "p.officePhone"},
 *          {"fax", "string", true, true, "p.fax"},
 *          {"emergency_phone", "string", true, true, "p.emergencyPhone"},
 *          {"email", "string", true, true, "p.email"},
 *          {"website_url", "string", true, true, "p.websiteUrl"},
 *          {"csz_str", "string", true, true, "CONCAT(csz.city, ' ', csz.stateAbbr, ', ', csz.zipMain)"},
 *          {"space", "string", true, true, "s.name"}
 *     },
 *     api_dashboard_physician_grid={
 *          {"id", "number", true, true, "p.id"},
 *          {"first_name", "string", true, true, "p.firstName"},
 *          {"middle_name", "string", true, true, "p.middleName"},
 *          {"last_name", "string", true, true, "p.lastName"},
 *          {"address_1", "string", true, true, "p.address_1"},
 *          {"address_2", "string", true, true, "p.address_2"},
 *          {"office_phone", "string", true, true, "p.officePhone"},
 *          {"fax", "string", true, true, "p.fax"},
 *          {"emergency_phone", "string", true, true, "p.emergencyPhone"},
 *          {"email", "string", true, true, "p.email"},
 *          {"website_url", "string", true, true, "p.websiteUrl"},
 *          {"csz_str", "string", true, true, "CONCAT(csz.city, ' ', csz.stateAbbr, ', ', csz.zipMain)"},
 *     }
 * )
 */
class Physician
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups({
     *     "api_dashboard_physician_list",
     *     "api_dashboard_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_grid",
     *     "api_admin_resident_list",
     *     "api_admin_resident_get"
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Space", inversedBy="spacePhysicians", cascade={"persist"})
     * @ORM\JoinColumn(name="id_space", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_dashboard_physician_add",
     *     "api_dashboard_physician_edit",
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Groups({
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     */
    private $space;

    /**
     * @var CityStateZip
     * @ORM\ManyToOne(targetEntity="CityStateZip")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_csz", referencedColumnName="id", onDelete="SET NULL")
     * })
     * @Assert\NotBlank(groups={
     *     "api_dashboard_physician_add",
     *     "api_dashboard_physician_edit",
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Groups({
     *     "api_dashboard_physician_list",
     *     "api_dashboard_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     */
    private $csz;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=40, nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_dashboard_physician_add",
     *     "api_dashboard_physician_edit",
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Groups({
     *     "api_dashboard_physician_list",
     *     "api_dashboard_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=40, nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_dashboard_physician_add",
     *     "api_dashboard_physician_edit",
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Groups({
     *     "api_dashboard_physician_list",
     *     "api_dashboard_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(name="middle_name", type="string", length=40, nullable=true)
     * @Groups({
     *     "api_dashboard_physician_list",
     *     "api_dashboard_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     */
    private $middleName;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_dashboard_physician_add",
     *     "api_dashboard_physician_edit",
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @ORM\Column(name="address_1", type="string", length=100, nullable=false)
     * @Groups({
     *     "api_dashboard_physician_list",
     *     "api_dashboard_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     */
    private $address_1;

    /**
     * @var string
     *
     * @ORM\Column(name="address_2", type="string", length=100, nullable=true)
     * @Groups({
     *     "api_dashboard_physician_list",
     *     "api_dashboard_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     */
    private $address_2;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_dashboard_physician_add",
     *     "api_dashboard_physician_edit",
     *     "api_admin_physician_add",
     *     "api_admin_physician_edit"
     * })
     * @Assert\Regex(
     *     pattern="/(\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$)/",
     *     groups={
     *          "api_dashboard_physician_add",
     *          "api_dashboard_physician_edit",
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     * })
     * @ORM\Column(name="office_phone", type="string", length=20, nullable=false)
     * @Groups({
     *     "api_dashboard_physician_list",
     *     "api_dashboard_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     */
    private $officePhone;

    /**
     * @var string
     * @ORM\Column(name="fax", type="string", length=20, nullable=true)
     * @Groups({
     *     "api_dashboard_physician_list",
     *     "api_dashboard_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     * @Assert\Regex(
     *     pattern="/(\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$)/",
     *     groups={
     *          "api_dashboard_physician_add",
     *          "api_dashboard_physician_edit",
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     * })
     */
    private $fax;

    /**
     * @var string
     * @ORM\Column(name="emergency_phone", type="string", length=20, nullable=true)
     * @Groups({
     *     "api_dashboard_physician_list",
     *     "api_dashboard_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     * @Assert\Regex(
     *     pattern="/(\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$)/",
     *     groups={
     *          "api_dashboard_physician_add",
     *          "api_dashboard_physician_edit",
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     * })
     */
    private $emergencyPhone;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     * @Groups({
     *     "api_dashboard_physician_list",
     *     "api_dashboard_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     * @Assert\Email(
     *     groups={
     *          "api_dashboard_physician_add",
     *          "api_dashboard_physician_edit",
     *          "api_admin_physician_add",
     *          "api_admin_physician_edit"
     *     }
     * )
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="website_url", type="string", length=255, nullable=true)
     * @Groups({
     *     "api_dashboard_physician_list",
     *     "api_dashboard_physician_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get"
     * })
     */
    private $websiteUrl;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSpace()
    {
        return $this->space;
    }

    /**
     * @param mixed $space
     */
    public function setSpace($space): void
    {
        $this->space = $space;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    /**
     * @param string $middleName
     */
    public function setMiddleName(string $middleName): void
    {
        $this->middleName = $middleName;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address_1;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address_2;
    }

    /**
     * @return string
     */
    public function getOfficePhone()
    {
        return $this->officePhone;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @return string
     */
    public function getEmergencyPhone()
    {
        return $this->emergencyPhone;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getWebsiteUrl()
    {
        return $this->websiteUrl;
    }

    /**
     * @return CityStateZip
     */
    public function getCsz()
    {
        return $this->csz;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $address_1
     */
    public function setAddress1($address_1)
    {
        $this->address_1 = $address_1;
    }

    /**
     * @param string $address_2
     */
    public function setAddress2($address_2)
    {
        $this->address_2 = $address_2;
    }

    /**
     * @param string $officePhone
     */
    public function setOfficePhone($officePhone)
    {
        $this->officePhone = $officePhone;
    }

    /**
     * @param string $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * @param string $emergencyPhone
     */
    public function setEmergencyPhone($emergencyPhone)
    {
        $this->emergencyPhone = $emergencyPhone;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param string $websiteUrl
     */
    public function setWebsiteUrl($websiteUrl)
    {
        $this->websiteUrl = $websiteUrl;
    }

    /**
     * @param CityStateZip $csz
     */
    public function setCsz($csz)
    {
        $this->csz = $csz;
    }
}