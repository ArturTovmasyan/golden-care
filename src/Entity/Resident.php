<?php

namespace App\Entity;

use App\Entity\Physician;
use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_resident")
 * @ORM\Entity(repositoryClass="App\Repository\ResidentRepository")
 * @Grid(
 *     api_admin_resident_grid={
 *          {"id",         "number", true, true, "r.id"},
 *          {"first_name", "string", true, true, "r.first_name"},
 *          {"last_name",  "string", true, true, "r.last_name"},
 *          {"middle_name","string", true, true, "r.middle_name"},
 *          {"space_id","string", true, true, "r.space_id"},
 *          {"physician_id","string", true, true, "r.physician_id"},
 *          {"gender","number", true, true, "r.gender"},
 *          {"birthday","number", true, true, "r.birthday"},
 *     },
 *     api_dashboard_resident_grid={
 *
 *     }
 * )
 */
class Resident
{
    use TimeAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_resident_diet_list",
     *      "api_admin_resident_diet_get"
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Space", cascade={"persist"})
     * @ORM\JoinColumn(name="id_space", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $space;

    /**
     * @var Physician
     * @ORM\ManyToOne(targetEntity="App\Entity\Physician")
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_physician", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $physician;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=40, nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=40, nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(name="middle_name", type="string", length=40, nullable=true)
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $middleName;

    /**
     * @var string
     * @ORM\Column(name="photo", type="string", length=40, nullable=false)
     */
    private $photo = '';

    /**
     * @var string
     * @Assert\Image(
     *     maxSize="6000000",
     *     mimeTypes = {
     *          "image/jpeg",
     *          "image/jpg",
     *          "image/gif",
     *          "image/png"
     *     }
     * )
     */
    private $file;

    /**
     * @var \DateTime
     * @ORM\Column(name="birthday", type="datetime", nullable=false)
     * @Assert\DateTime(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $birthday;

    /**
     * @var int
     * @ORM\Column(name="gender", type="smallint", nullable=false)
     * @Assert\Choice({"1", "2"}, groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $gender;

    /**
     * @todo implement after resident events
     * @var \DateTime
     * @ORM\Column(name="date_admitted", type="datetime", nullable=true)
     */
    private $dateAdmitted;

    /**
     * @todo implement after resident events
     * @var \DateTime
     * @ORM\Column(name="date_left", type="datetime", nullable=true)
     */
    private $dateLeft;

    /**
     * @todo implement after resident events
     * @var int
     * @ORM\Column(name="state", type="smallint", nullable=false)
     * @Groups({
     *      "api_admin_resident_grid",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $state = \App\Model\Resident::ACTIVE;

    /**
     * @return int
     */
    public function getId(): int
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
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
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
    public function setFirstName($firstName): void
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
    public function setLastName($lastName): void
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
    public function setMiddleName($middleName): void
    {
        $this->middleName = $middleName;
    }

    /**
     * @return \App\Entity\Physician
     */
    public function getPhysician(): \App\Entity\Physician
    {
        return $this->physician;
    }

    /**
     * @param \App\Entity\Physician $physician
     */
    public function setPhysician(\App\Entity\Physician $physician): void
    {
        $this->physician = $physician;
    }

    /**
     * @return string
     */
    public function getPhoto(): string
    {
        return $this->photo;
    }

    /**
     * @param string $photo
     */
    public function setPhoto($photo): void
    {
        $this->photo = $photo;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file): void
    {
        $this->file = $file;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday(): \DateTime
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $birthday
     */
    public function setBirthday($birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @return int
     */
    public function getGender(): int
    {
        return $this->gender;
    }

    /**
     * @param int $gender
     */
    public function setGender($gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @return \DateTime
     */
    public function getDateAdmitted(): \DateTime
    {
        return $this->dateAdmitted;
    }

    /**
     * @param \DateTime $dateAdmitted
     */
    public function setDateAdmitted(\DateTime $dateAdmitted): void
    {
        $this->dateAdmitted = $dateAdmitted;
    }

    /**
     * @return \DateTime
     */
    public function getDateLeft(): \DateTime
    {
        return $this->dateLeft;
    }

    /**
     * @param \DateTime $dateLeft
     */
    public function setDateLeft(\DateTime $dateLeft): void
    {
        $this->dateLeft = $dateLeft;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState(int $state): void
    {
        $this->state = $state;
    }
}
