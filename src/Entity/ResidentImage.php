<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

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
     *     "api_admin_resident_image_edit",
     *     "api_admin_resident_image_add_mobile"
     * })
     * @ORM\OneToOne(targetEntity="App\Entity\Resident")
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
     *     "api_admin_resident_image_edit",
     *     "api_admin_resident_image_add_mobile"
     * })
     * @Groups({
     *     "api_admin_resident_image_list",
     *     "api_admin_resident_image_get",
     *     "api_admin_resident_get"
     * })
     */
    private $photo;

    /**
     * @var string $photo
     * @ORM\Column(name="photo_35_35", type="text")
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_image_add",
     *     "api_admin_resident_image_edit",
     *     "api_admin_resident_image_add_mobile"
     * })
     * @Groups({
     *     "api_admin_resident_image_list",
     *     "api_admin_resident_image_get"
     * })
     */
    private $photo_35_35;

    /**
     * @var string $photo
     * @ORM\Column(name="photo_150_150", type="text")
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_image_add",
     *     "api_admin_resident_image_edit",
     *     "api_admin_resident_image_add_mobile"
     * })
     * @Groups({
     *     "api_admin_resident_image_list",
     *     "api_admin_resident_image_get",
     *     "api_admin_resident_list"
     * })
     */
    private $photo_150_150;

    /**
     * @var string $photo
     * @ORM\Column(name="photo_300_300", type="text")
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_image_add",
     *     "api_admin_resident_image_edit",
     *     "api_admin_resident_image_add_mobile"
     * })
     * @Groups({
     *     "api_admin_resident_image_list",
     *     "api_admin_resident_image_get"
     * })
     */
    private $photo_300_300;

    /**
     * @var string
     *
     * @ORM\Column(name="request_id", type="string", length=255, nullable=true)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_image_add_mobile"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Request Id cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_image_add_mobile"
     * })
     */
    private $requestId = '';

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
    public function getPhoto3535(): ?string
    {
        return $this->photo_35_35;
    }

    /**
     * @param null|string $photo_35_35
     */
    public function setPhoto3535(?string $photo_35_35): void
    {
        $this->photo_35_35 = $photo_35_35;
    }

    /**
     * @return null|string
     */
    public function getPhoto150150(): ?string
    {
        return $this->photo_150_150;
    }

    /**
     * @param null|string $photo_150_150
     */
    public function setPhoto150150(?string $photo_150_150): void
    {
        $this->photo_150_150 = $photo_150_150;
    }

    /**
     * @return null|string
     */
    public function getPhoto300300(): ?string
    {
        return $this->photo_300_300;
    }

    /**
     * @param null|string $photo_300_300
     */
    public function setPhoto300300(?string $photo_300_300): void
    {
        $this->photo_300_300 = $photo_300_300;
    }

    /**
     * @return null|string
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * @param null|string $requestId
     */
    public function setRequestId(?string $requestId): void
    {
        $this->requestId = $requestId;
    }
}
