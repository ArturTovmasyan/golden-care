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
 * Class Salutation
 *
 * @ORM\Entity(repositoryClass="App\Repository\SalutationRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space.",
 *     groups={
 *          "api_admin_salutation_add",
 *          "api_admin_salutation_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_salutation")
 * @Grid(
 *     api_admin_salutation_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "sa.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "sa.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class Salutation
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_salutation_list",
     *     "api_admin_salutation_get",
     *     "api_admin_resident_list",
     *     "api_admin_resident_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_salutation_add",
     *     "api_admin_salutation_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_salutation_add",
     *          "api_admin_salutation_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_salutation_grid",
     *     "api_admin_salutation_list",
     *     "api_admin_salutation_get",
     *     "api_admin_resident_list",
     *     "api_admin_resident_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_resident_event_list",
     *     "api_admin_resident_event_get",
     *     "api_admin_responsible_person_list",
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_physician_list",
     *     "api_admin_resident_physician_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_salutation_add",
     *     "api_admin_salutation_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="salutations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_salutation_grid",
     *     "api_admin_salutation_list",
     *     "api_admin_salutation_get",
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Physician", mappedBy="salutation", cascade={"remove", "persist"})
     */
    private $physicians;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Resident", mappedBy="salutation", cascade={"remove", "persist"})
     */
    private $residents;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResponsiblePerson", mappedBy="salutation", cascade={"remove", "persist"})
     */
    private $responsiblePersons;

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $title = preg_replace('/\s\s+/', ' ', $title);
        $this->title = $title;
    }

    /**
     * @return Space|null
     */
    public function getSpace(): ?Space
    {
        return $this->space;
    }

    /**
     * @param Space|null $space
     */
    public function setSpace(?Space $space): void
    {
        $this->space = $space;
    }

    /**
     * @return ArrayCollection
     */
    public function getPhysicians(): ArrayCollection
    {
        return $this->physicians;
    }

    /**
     * @param ArrayCollection $physicians
     */
    public function setPhysicians(ArrayCollection $physicians): void
    {
        $this->physicians = $physicians;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidents(): ArrayCollection
    {
        return $this->residents;
    }

    /**
     * @param ArrayCollection $residents
     */
    public function setResidents(ArrayCollection $residents): void
    {
        $this->residents = $residents;
    }

    /**
     * @return ArrayCollection
     */
    public function getResponsiblePersons(): ArrayCollection
    {
        return $this->responsiblePersons;
    }

    /**
     * @param ArrayCollection $responsiblePersons
     */
    public function setResponsiblePersons(ArrayCollection $responsiblePersons): void
    {
        $this->responsiblePersons = $responsiblePersons;
    }
}
