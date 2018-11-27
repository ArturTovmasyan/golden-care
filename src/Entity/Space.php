<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid as Grid;

/**
 * @ORM\Table(name="tbl_space")
 * @ORM\Entity(repositoryClass="App\Repository\SpaceRepository")
 * @UniqueEntity(fields="name", message="Sorry, this name is already in use.", groups={"api_dashboard_space_edit"})
 * @Grid(
 *     api_admin_space_grid={
 *          {"id", "number", true, true, "s.id"},
 *          {"name", "string", true, true, "s.name"},
 *          {"created_at", "date", true, true, "s.createdAt"}
 *     }
 * )
 */
class Space
{
    use TimeAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     *     "api_admin_space_grid",
     *     "api_admin_space_list",
     *     "api_admin_space_get",
     *     "api_dashboard_space_user_get",
     *     "api_profile_me",
     *     "api_dashboard_space_get",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_facility_list",
     *     "api_admin_facility_get",
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get",
     *     "api_admin_region_list",
     *     "api_admin_region_get",
     *     "api_admin_resident_grid",
     *     "api_admin_resident_list",
     *     "api_admin_resident_get",
     *     "api_admin_physician_speciality_list",
     *     "api_admin_physician_speciality_get",
     *     "api_admin_responsible_person_list",
     *     "api_admin_responsible_person_list_by_space",
     *     "api_admin_responsible_person_get",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", unique=true, length=255)
     * @Groups({
     *     "api_admin_role_grid",
     *     "api_admin_role_list",
     *     "api_admin_role_get",
     *     "api_admin_space_grid",
     *     "api_admin_space_list",
     *     "api_admin_space_get",
     *     "api_dashboard_space_user_get",
     *     "api_profile_me",
     *     "api_dashboard_space_get",
     *     "api_dashboard_physician_grid",
     *     "api_dashboard_physician_list",
     *     "api_admin_physician_list",
     *     "api_admin_physician_get",
     *     "api_admin_facility_list",
     *     "api_admin_facility_get",
     *     "api_admin_apartment_list",
     *     "api_admin_apartment_get",
     *     "api_admin_region_list",
     *     "api_admin_region_get",
     *     "api_admin_physician_speciality_list",
     *     "api_admin_physician_speciality_get",
     *     "api_admin_payment_source_list",
     *     "api_admin_payment_source_get"
     * })
     * @Assert\NotBlank(groups={"api_dashboard_space_edit", "api_admin_space_edit"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="SpaceUserRole", mappedBy="space", cascade={"persist", "remove"})
     */
    protected $spaceUserRoles;

    /**
     * @ORM\OneToMany(targetEntity="Physician", mappedBy="space", cascade={"persist", "remove"})
     */
    protected $spacePhysicians;

    /**
     * Space constructor.
     */
    public function __construct()
    {
        $this->spaceUserRoles  = new ArrayCollection();
        $this->spacePhysicians = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return ArrayCollection
     */
    public function getSpaceUserRoles()
    {
        return $this->spaceUserRoles;
    }

    /**
     * @return ArrayCollection
     */
    public function getSpacePhysicians()
    {
        return $this->spaceUserRoles;
    }
}
