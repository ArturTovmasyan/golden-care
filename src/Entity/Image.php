<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Class Image
 *
 * @ORM\Entity(repositoryClass="App\Repository\ImageRepository")
 * @ORM\Table(name="tbl_image")
 */
class Image
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     * @Groups({
     *     "api_admin_image_list",
     *     "api_admin_image_get"
     * })
     */
    protected $id;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_image_add",
     *     "api_admin_resident_image_edit",
     *     "api_admin_resident_image_add_mobile",
     *     "api_admin_user_image_add",
     *     "api_admin_user_image_edit"
     * })
     * @Assert\Choice(
     *     callback={"App\Model\FileType","getTypeValues"},
     *     groups={
     *         "api_admin_resident_image_add",
     *         "api_admin_resident_image_edit",
     *         "api_admin_resident_image_add_mobile",
     *         "api_admin_user_image_add",
     *         "api_admin_user_image_edit"
     * })
     * @ORM\Column(name="type", type="integer", length=1)
     * @Groups({
     *     "api_admin_image_list",
     *     "api_admin_image_get"
     * })
     */
    private $type;

    /**
     * @var string $mimeType
     * @ORM\Column(name="mime_type", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_image_add",
     *     "api_admin_resident_image_edit",
     *     "api_admin_resident_image_add_mobile",
     *     "api_admin_user_image_add",
     *     "api_admin_user_image_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "MimeType cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_image_add",
     *          "api_admin_image_edit",
     *          "api_admin_resident_image_add_mobile",
     *          "api_admin_user_image_add",
     *          "api_admin_user_image_edit"
     * })
     * @Groups({
     *     "api_admin_image_list",
     *     "api_admin_image_get"
     * })
     */
    private $mimeType;

    /**
     * @var string $s3Id
     * @ORM\Column(name="s3Id", type="string", length=128, nullable=true)
     * @Assert\Length(
     *      max = 128,
     *      maxMessage = "S3 Id cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_image_add",
     *          "api_admin_resident_image_edit",
     *          "api_admin_resident_image_add_mobile",
     *          "api_admin_user_image_add",
     *          "api_admin_user_image_edit"
     * })
     * @Groups({
     *     "api_admin_image_list",
     *     "api_admin_image_get"
     * })
     */
    private $s3Id;

    /**
     * @var string $s3Id
     * @ORM\Column(name="s3Id_35_35", type="string", length=128, nullable=true)
     * @Assert\Length(
     *      max = 128,
     *      maxMessage = "S3 Id 35*35 cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_image_add",
     *          "api_admin_resident_image_edit",
     *          "api_admin_resident_image_add_mobile",
     *          "api_admin_user_image_add",
     *          "api_admin_user_image_edit"
     * })
     * @Groups({
     *     "api_admin_image_list",
     *     "api_admin_image_get"
     * })
     */
    private $s3Id_35_35;

    /**
     * @var string $s3Id
     * @ORM\Column(name="s3Id_150_150", type="string", length=128, nullable=true)
     * @Assert\Length(
     *      max = 128,
     *      maxMessage = "S3 Id 150*150 cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_image_add",
     *          "api_admin_resident_image_edit",
     *          "api_admin_resident_image_add_mobile",
     *          "api_admin_user_image_add",
     *          "api_admin_user_image_edit"
     * })
     * @Groups({
     *     "api_admin_image_list",
     *     "api_admin_image_get"
     * })
     */
    private $s3Id_150_150;

    /**
     * @var string $s3Id
     * @ORM\Column(name="s3Id_300_300", type="string", length=128, nullable=true)
     * @Assert\Length(
     *      max = 128,
     *      maxMessage = "S3 Id 300*300 cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_resident_image_add",
     *          "api_admin_resident_image_edit",
     *          "api_admin_resident_image_add_mobile",
     *          "api_admin_user_image_add",
     *          "api_admin_user_image_edit"
     * })
     * @Groups({
     *     "api_admin_image_list",
     *     "api_admin_image_get"
     * })
     */
    private $s3Id_300_300;

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
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_image_add",
     *     "api_admin_resident_image_edit",
     *     "api_admin_resident_image_add_mobile"
     * })
     * @ORM\OneToOne(targetEntity="App\Entity\Resident", inversedBy="image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_image_list",
     *     "api_admin_image_get"
     * })
     */
    private $resident;

    /**
     * @var User
     * @Assert\NotNull(message = "Please select an User", groups={
     *     "api_admin_user_image_add",
     *     "api_admin_user_image_edit"
     * })
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_image_list",
     *     "api_admin_image_get"
     * })
     */
    private $user;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return null|string
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @param null|string $mimeType
     */
    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @return null|string
     */
    public function getS3Id(): ?string
    {
        return $this->s3Id;
    }

    /**
     * @param null|string $s3Id
     */
    public function setS3Id(?string $s3Id): void
    {
        $this->s3Id = $s3Id;
    }

    /**
     * @return null|string
     */
    public function getS3Id3535(): ?string
    {
        return $this->s3Id_35_35;
    }

    /**
     * @param null|string $s3Id_35_35
     */
    public function setS3Id3535(?string $s3Id_35_35): void
    {
        $this->s3Id_35_35 = $s3Id_35_35;
    }

    /**
     * @return null|string
     */
    public function getS3Id150150(): ?string
    {
        return $this->s3Id_150_150;
    }

    /**
     * @param null|string $s3Id_150_150
     */
    public function setS3Id150150(?string $s3Id_150_150): void
    {
        $this->s3Id_150_150 = $s3Id_150_150;
    }

    /**
     * @return null|string
     */
    public function getS3Id300300(): ?string
    {
        return $this->s3Id_300_300;
    }

    /**
     * @param null|string $s3Id_300_300
     */
    public function setS3Id300300(?string $s3Id_300_300): void
    {
        $this->s3Id_300_300 = $s3Id_300_300;
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
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}
