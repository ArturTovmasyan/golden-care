<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="tbl_resident")
 * @ORM\Entity(repositoryClass="App\Repository\ResidentRepository")
 * @Grid(
 *     api_admin_resident_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "r.id"
 *          },
 *          {
 *              "id"         = "full_name",
 *              "type"       = "string",
 *              "field"      = "CONCAT(sal.title, ' ', r.firstName, ' ', r.middleName, ' ', r.lastName)",
 *              "link"       = "/resident/:id"
 *          },
 *          {
 *              "id"         = "gender",
 *              "type"       = "enum",
 *              "field"      = "r.gender",
 *              "values"     = "\App\Model\User::genderValues"
 *          },
 *          {
 *              "id"         = "birthday",
 *              "type"       = "date",
 *              "field"      = "r.birthday"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
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
     *      "api_admin_resident_diet_get",
     *      "api_admin_resident_medication_list",
     *      "api_admin_resident_medication_get",
     *      "api_admin_resident_medication_allergy_list",
     *      "api_admin_resident_medication_allergy_get",
     *      "api_admin_resident_allergen_list",
     *      "api_admin_resident_allergen_get",
     *      "api_admin_resident_medical_history_condition_list",
     *      "api_admin_resident_medical_history_condition_get",
     *      "api_admin_resident_diagnosis_list",
     *      "api_admin_resident_diagnosis_get",
     *      "api_admin_resident_list_by_params",
     *      "api_admin_resident_responsible_person_list",
     *      "api_admin_resident_responsible_person_get",
     *      "api_admin_resident_physician_list",
     *      "api_admin_resident_physician_get",
     *      "api_admin_resident_rent_list",
     *      "api_admin_resident_rent_get",
     *      "api_admin_resident_event_list",
     *      "api_admin_resident_event_get",
     *      "api_admin_contract_list",
     *      "api_admin_contract_get",
     *      "api_admin_facility_room_list",
     *      "api_admin_facility_room_get"
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
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $space;

    /**
     * @ORM\ManyToOne(targetEntity="Salutation", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_salutation", referencedColumnName="id", nullable=false)
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $salutation;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=40, nullable=false)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_resident_list_by_params",
     *      "api_admin_resident_responsible_person_list",
     *      "api_admin_resident_responsible_person_get",
     *      "api_admin_resident_physician_list",
     *      "api_admin_resident_physician_get"
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
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_resident_list_by_params",
     *      "api_admin_resident_responsible_person_list",
     *      "api_admin_resident_responsible_person_get",
     *      "api_admin_resident_physician_list",
     *      "api_admin_resident_physician_get"
     * })
     */
    private $lastName;

    /**
     * @var string
     * @ORM\Column(name="middle_name", type="string", length=40, nullable=true)
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $middleName;

    /**
     * @var string
     * @Groups({
     *      "api_admin_resident_get"
     * })
     */
    private $photo = "";

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
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $birthday;

    /**
     * @var int
     * @ORM\Column(name="gender", type="smallint", nullable=false)
     * @Assert\Choice(
     *      callback={"App\Model\User","getGenderValues"},
     *      groups={
     *          "api_admin_resident_add",
     *          "api_admin_resident_edit"
     *      }
     * )
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $gender;

    /**
     * @ORM\OneToMany(targetEntity="ResidentPhone", mappedBy="resident")
     * @Assert\Valid(groups={
     *      "api_admin_resident_add",
     *      "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $phones;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Assessment\Assessment", mappedBy="resident")
     */
    private $assessments;

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
     * @return Salutation
     */
    public function getSalutation(): Salutation
    {
        return $this->salutation;
    }

    /**
     * @param Salutation $salutation
     */
    public function setSalutation(Salutation $salutation): void
    {
        $this->salutation = $salutation;
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
     * @return string
     */
    public function getPhoto()
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
     * @return mixed
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param mixed $phones
     */
    public function setPhones($phones): void
    {
        $this->phones = $phones;
    }

    /**
     * @return mixed
     */
    public function getAssessments()
    {
        return $this->assessments;
    }

    /**
     * @param mixed $assessments
     */
    public function setAssessments($assessments): void
    {
        $this->assessments = $assessments;
    }
}
