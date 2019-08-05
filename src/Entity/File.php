<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Class File
 *
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 * @ORM\Table(name="tbl_file")
 */
class File
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     * @Groups({
     *     "api_admin_file_list",
     *     "api_admin_file_get"
     * })
     */
    protected $id;

    /**
     * @var int
     * @Assert\NotBlank(groups={
     *     "api_admin_file_add",
     *     "api_admin_file_edit"
     * })
     * @Assert\Choice(
     *     callback={"App\Model\FileType","getTypeValues"},
     *     groups={
     *         "api_admin_file_add",
     *         "api_admin_file_edit"
     * })
     * @ORM\Column(name="type", type="integer", length=1)
     * @Groups({
     *     "api_admin_file_list",
     *     "api_admin_file_get"
     * })
     */
    private $type;

    /**
     * @var string $mimeType
     * @ORM\Column(name="mime_type", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_admin_file_add",
     *     "api_admin_file_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "MimeType cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_file_add",
     *          "api_admin_file_edit"
     * })
     * @Groups({
     *     "api_admin_file_list",
     *     "api_admin_file_get"
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
     *          "api_admin_file_add",
     *          "api_admin_file_edit"
     * })
     * @Groups({
     *     "api_admin_file_list",
     *     "api_admin_file_get"
     * })
     */
    private $s3Id;

    /**
     * @var Document
     * @ORM\OneToOne(targetEntity="App\Entity\Document", mappedBy="file", cascade={"remove", "persist"})
     */
    private $document;

    /**
     * @var ResidentDocument
     * @ORM\OneToOne(targetEntity="App\Entity\ResidentDocument", mappedBy="file", cascade={"remove", "persist"})
     */
    private $residentDocument;

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
     * @return Document|null
     */
    public function getDocument(): ?Document
    {
        return $this->document;
    }

    /**
     * @param Document|null $document
     */
    public function setDocument(?Document $document): void
    {
        $this->document = $document;
    }

    /**
     * @return ResidentDocument|null
     */
    public function getResidentDocument(): ?ResidentDocument
    {
        return $this->residentDocument;
    }

    /**
     * @param ResidentDocument|null $residentDocument
     */
    public function setResidentDocument(?ResidentDocument $residentDocument): void
    {
        $this->residentDocument = $residentDocument;
    }
}
