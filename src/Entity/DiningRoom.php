<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class DiningRoom
 *
 * @ORM\Entity(repositoryClass="App\Repository\DiningRoomRepository")
 * @UniqueEntity(
 *     fields={"facility", "title"},
 *     errorPath="number",
 *     message="This title is already in use on that facility",
 *     groups={
 *         "api_admin_dining_room_add",
 *         "api_admin_dining_room_edit"
 * })
 * @ORM\Table(name="tbl_dining_room")
 * @Grid(
 *     api_admin_dining_room_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "dr.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "dr.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "facility",
 *              "type"       = "string",
 *              "field"      = "f.name"
 *          }
 *     }
 * )
 */
class DiningRoom
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_dining_room_grid",
     *     "api_admin_dining_room_list",
     *     "api_admin_dining_room_get",
     *     "api_admin_resident_grid",
     *     "api_admin_resident_list",
     *     "api_admin_resident_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_dining_room_add",
     *     "api_admin_dining_room_edit"
     * })
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_dining_room_add",
     *          "api_admin_dining_room_edit"
     * })
     * @ORM\Column(name="title", type="string", length=50)
     * @Groups({
     *     "api_admin_dining_room_grid",
     *     "api_admin_dining_room_list",
     *     "api_admin_dining_room_get",
     *     "api_admin_resident_get",
     *     "api_admin_resident_admission_list",
     *     "api_admin_resident_admission_get",
     *     "api_admin_contract_list",
     *     "api_admin_contract_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $title;

    /**
     * @var Facility
     * @Assert\NotNull(message = "Please select a Facility", groups={
     *     "api_admin_dining_room_add",
     *     "api_admin_dining_room_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Facility")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_dining_room_grid",
     *     "api_admin_dining_room_list",
     *     "api_admin_dining_room_get",
     *     "api_admin_contract_get_active"
     * })
     */
    private $facility;

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
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = preg_replace('/\s\s+/', ' ', $title);
    }

    /**
     * @return Facility|null
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * @param Facility|null $facility
     * @return DiningRoom
     */
    public function setFacility(?Facility $facility): void
    {
        $this->facility = $facility;
    }
}
