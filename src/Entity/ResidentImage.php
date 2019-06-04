<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ResidentImage
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentImageRepository")
 * @ORM\Table(name="tbl_resident_image")
 */
class ResidentImage
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_image_list",
     *     "api_admin_resident_image_get",
     *     "api_admin_resident_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_image_add",
     *     "api_admin_resident_image_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="images")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_image_list",
     *     "api_admin_resident_image_get"
     * })
     */
    private $resident;

    /**
     * @var string $photo
     * @ORM\Column(name="photo", type="text")
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_image_add",
     *     "api_admin_resident_image_edit"
     * })
     * @Groups({
     *     "api_admin_resident_image_list",
     *     "api_admin_resident_image_get",
     *     "api_admin_resident_get"
     * })
     */
    private $photo;

    /**
     * @var string $title
     * @ORM\Column(name="title", type="string")
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_image_add",
     *     "api_admin_resident_image_edit"
     * })
     * @Groups({
     *     "api_admin_resident_image_list",
     *     "api_admin_resident_image_get",
     *     "api_admin_resident_get"
     * })
     */
    private $title;

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
     * @return Resident|null
     */
    public function getResident(): ?Resident
    {
        return $this->resident;
    }

    /**
     * @param Resident|null $resident
     */
    public function setResident(?Resident $resident): void
    {
        $this->resident = $resident;
    }

    /**
     * @return null|string
     */
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    /**
     * @param null|string $photo
     */
    public function setPhoto(?string $photo): void
    {
        $this->photo = $photo;
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
        $this->title = $title;
    }
}
