<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class ResidentDocument
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentDocumentRepository")
 * @UniqueEntity(
 *     fields={"resident", "title"},
 *     errorPath="title",
 *     message="The title is already in use for this Resident.",
 *     groups={
 *          "api_admin_resident_document_add",
 *          "api_admin_resident_document_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_resident_document")
 * @Grid(
 *     api_admin_resident_document_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rd.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "rd.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "user",
 *              "type"       = "string",
 *              "field"      = "CONCAT(u.firstName, ' ', u.lastName)"
 *          },
 *          {
 *              "id"         = "date_modified",
 *              "type"       = "datetime",
 *              "field"      = "rd.updatedAt"
 *          },
 *          {
 *              "id"         = "date_created",
 *              "type"       = "datetime",
 *              "field"      = "rd.createdAt"
 *          }
 *     }
 * )
 */
class ResidentDocument
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_document_list",
     *     "api_admin_resident_document_get"
     * })
     */
    private $id;

    /**
     * @var Resident
     * @Assert\NotNull(message = "Please select a Resident", groups={
     *     "api_admin_resident_document_add",
     *     "api_admin_resident_document_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentDocuments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_document_list",
     *     "api_admin_resident_document_get"
     * })
     */
    private $resident;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_document_add",
     *     "api_admin_resident_document_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_resident_document_add",
     *           "api_admin_resident_document_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_resident_document_list",
     *     "api_admin_resident_document_get"
     * })
     */
    private $title;

    /**
     * @var File
     * @Assert\NotNull(message = "Please select a File", groups={
     *     "api_admin_resident_document_add",
     *     "api_admin_resident_document_edit"
     * })
     * @ORM\OneToOne(targetEntity="App\Entity\File", inversedBy="residentDocument")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_file", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $file;

    /**
     * @var string $downloadUrl
     */
    private $downloadUrl;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("file")
     * @Serializer\Groups({
     *     "api_admin_resident_document_list",
     *     "api_admin_resident_document_get"
     * })
     */
    public function getResidentDocumentFile(): ?string
    {
        if ($this->getFile() !== null) {
            return $this->getDownloadUrl();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("extension")
     * @Serializer\Groups({
     *     "api_admin_resident_document_list",
     *     "api_admin_resident_document_get"
     * })
     */
    public function getResidentDocumentFileExtension(): ?string
    {
        if ($this->getFile() !== null) {
            return $this->getFile()->getExtension();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("date_created")
     * @Serializer\Groups({
     *     "api_admin_resident_document_list",
     *     "api_admin_resident_document_get"
     * })
     */
    public function getDateCreated(): ?\DateTime
    {
        return $this->getCreatedAt();
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("date_modified")
     * @Serializer\Groups({
     *     "api_admin_resident_document_list",
     *     "api_admin_resident_document_get"
     * })
     */
    public function getDateModified(): ?\DateTime
    {
        return $this->getUpdatedAt();
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("owner")
     * @Serializer\Groups({
     *     "api_admin_resident_document_list",
     *     "api_admin_resident_document_get"
     * })
     */
    public function getOwner(): ?string
    {
        if ($this->getUpdatedBy() !== null) {
            return $this->getUpdatedBy()->getFirstName() . ' ' . $this->getUpdatedBy()->getLastName();
        }

        return null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

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
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File|null $file
     */
    public function setFile(?File $file): void
    {
        $this->file = $file;
    }

    /**
     * @return null|string
     */
    public function getDownloadUrl(): ?string
    {
        return $this->downloadUrl;
    }

    /**
     * @param null|string $downloadUrl
     */
    public function setDownloadUrl(?string $downloadUrl): void
    {
        $this->downloadUrl = $downloadUrl;
    }
}
