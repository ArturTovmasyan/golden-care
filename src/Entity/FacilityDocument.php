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
 * Class FacilityDocument
 *
 * @ORM\Entity(repositoryClass="App\Repository\FacilityDocumentRepository")
 * @UniqueEntity(
 *     fields={"facility", "title"},
 *     errorPath="title",
 *     message="The title is already in use for this Facility.",
 *     groups={
 *          "api_admin_facility_document_add",
 *          "api_admin_facility_document_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_facility_document")
 * @Grid(
 *     api_admin_facility_document_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "fd.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "fd.title",
 *              "link"       = ":download"
 *          },
 *          {
 *              "id"         = "category",
 *              "type"       = "string",
 *              "field"      = "dc.title"
 *          },
 *          {
 *              "id"         = "facility",
 *              "type"       = "string",
 *              "field"      = "f.name"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(fd.description, 1, 100)), CASE WHEN LENGTH(fd.description) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "user",
 *              "type"       = "string",
 *              "field"      = "CONCAT(u.firstName, ' ', u.lastName)"
 *          },
 *          {
 *              "id"         = "date_modified",
 *              "type"       = "datetime",
 *              "field"      = "fd.updatedAt"
 *          },
 *          {
 *              "id"         = "date_created",
 *              "type"       = "datetime",
 *              "field"      = "fd.createdAt"
 *          }
 *     }
 * )
 */
class FacilityDocument
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get"
     * })
     */
    private $id;

    /**
     * @var Facility
     * @Assert\NotNull(message = "Please select a Facility", groups={
     *     "api_admin_facility_document_add",
     *     "api_admin_facility_document_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Facility", inversedBy="facilityDocuments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get"
     * })
     */
    private $facility;

    /**
     * @var DocumentCategory
     * @ORM\ManyToOne(targetEntity="App\Entity\DocumentCategory", inversedBy="facilityDocuments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_category", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Document Category", groups={
     *     "api_admin_facility_document_add",
     *     "api_admin_facility_document_edit"
     * })
     * @Groups({
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get"
     * })
     */
    private $category;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_facility_document_add",
     *     "api_admin_facility_document_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_facility_document_add",
     *           "api_admin_facility_document_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get"
     * })
     */
    private $title;

    /**
     * @var string $description
     * @ORM\Column(name="description", type="text", length=512, nullable=true)
     * @Assert\Length(
     *      max = 512,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_facility_document_add",
     *          "api_admin_facility_document_edit"
     * })
     * @Groups({
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get"
     * })
     */
    private $description;

    /**
     * @var File
     * @Assert\NotNull(message = "Please select a File", groups={
     *     "api_admin_facility_document_add",
     *     "api_admin_facility_document_edit"
     * })
     * @ORM\OneToOne(targetEntity="App\Entity\File", inversedBy="facilityDocument")
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
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get"
     * })
     */
    public function getFacilityDocumentFile(): ?string
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
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get"
     * })
     */
    public function getFacilityDocumentFileExtension(): ?string
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
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get"
     * })
     */
    public function getDateCreated(): ?\DateTime
    {
        return $this->getCreatedAt();
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("owner")
     * @Serializer\Groups({
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get"
     * })
     */
    public function getOwner(): ?string
    {
        if ($this->getUpdatedBy() !== null) {
            return $this->getUpdatedBy()->getFirstName() . ' ' . $this->getUpdatedBy()->getLastName();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("date_modified")
     * @Serializer\Groups({
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get"
     * })
     */
    public function getDateModified(): ?\DateTime
    {
        return $this->getUpdatedAt();
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
     * @return Facility|null
     */
    public function getFacility(): ?Facility
    {
        return $this->facility;
    }

    /**
     * @param Facility|null $facility
     */
    public function setFacility(?Facility $facility): void
    {
        $this->facility = $facility;
    }

    /**
     * @return DocumentCategory|null
     */
    public function getCategory(): ?DocumentCategory
    {
        return $this->category;
    }

    /**
     * @param DocumentCategory|null $category
     */
    public function setCategory(?DocumentCategory $category): void
    {
        $this->category = $category;
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
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
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
