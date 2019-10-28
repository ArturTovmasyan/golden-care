<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use App\Annotation\Grid;

/**
 * Class Document
 *
 * @ORM\Entity(repositoryClass="App\Repository\DocumentRepository")
 * @ORM\Table(name="tbl_document")
 * @Grid(
 *     api_admin_document_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "d.id"
 *          },
 *          {
 *              "id"         = "category",
 *              "type"       = "string",
 *              "field"      = "dc.title"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "d.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "CONCAT(TRIM(SUBSTRING(d.description, 1, 100)), CASE WHEN LENGTH(d.description) > 100 THEN '…' ELSE '' END)"
 *          },
 *          {
 *              "id"         = "date_uploaded",
 *              "type"       = "date",
 *              "field"      = "d.updatedAt"
 *          },
 *          {
 *              "id"         = "owner",
 *              "type"       = "string",
 *              "field"      = "CONCAT(COALESCE(u.firstName, ''), ' ', COALESCE(u.lastName, ''))"
 *          }
 *     }
 * )
 */
class Document
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_document_list",
     *     "api_admin_document_get"
     * })
     */
    private $id;

    /**
     * @var DocumentCategory
     * @ORM\ManyToOne(targetEntity="App\Entity\DocumentCategory", inversedBy="documents")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_category", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(message = "Please select a Document Category", groups={
     *     "api_admin_document_add",
     *     "api_admin_document_edit"
     * })
     * @Groups({
     *     "api_admin_document_list",
     *     "api_admin_document_get"
     * })
     */
    private $category;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_document_add",
     *     "api_admin_document_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_document_add",
     *           "api_admin_document_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_document_list",
     *     "api_admin_document_get"
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
     *          "api_admin_document_add",
     *          "api_admin_document_edit"
     * })
     * @Groups({
     *     "api_admin_document_list",
     *     "api_admin_document_get"
     * })
     */
    private $description;

    /**
     * @var ArrayCollection
     * @Assert\NotNull(message = "Please select at least one Facility.", groups={
     *     "api_admin_document_add",
     *     "api_admin_document_edit"
     * })
     * @ORM\ManyToMany(targetEntity="App\Entity\Facility", inversedBy="documents", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_document_facilities",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_document", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_facility", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_admin_document_list",
     *     "api_admin_document_get"
     * })
     */
    private $facilities;

    /**
     * @var ArrayCollection
     * @Assert\NotNull(message = "Please select at least one Role.", groups={
     *     "api_admin_document_add",
     *     "api_admin_document_edit"
     * })
     * @ORM\ManyToMany(targetEntity="App\Entity\Role", inversedBy="documents", cascade={"persist"})
     * @ORM\JoinTable(
     *      name="tbl_document_roles",
     *      joinColumns={
     *          @ORM\JoinColumn(name="id_document", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="id_role", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     * @Groups({
     *     "api_admin_document_list",
     *     "api_admin_document_get"
     * })
     */
    private $roles;

    /**
     * @var File
     * @Assert\NotNull(message = "Please select a File", groups={
     *     "api_admin_document_add",
     *     "api_admin_document_edit"
     * })
     * @ORM\OneToOne(targetEntity="App\Entity\File", inversedBy="document")
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
     *     "api_admin_document_list",
     *     "api_admin_document_get"
     * })
     * @return null|string
     */
    public function getDocumentFile(): ?string
    {
        if ($this->getFile() !== null) {
            return $this->getDownloadUrl();
        }

        return null;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("date_uploaded")
     * @Serializer\Groups({
     *     "api_admin_document_list",
     *     "api_admin_document_get"
     * })
     */
    public function getDateUploaded(): ?\DateTime
    {
        return $this->getUpdatedAt();
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("owner")
     * @Serializer\Groups({
     *     "api_admin_document_list",
     *     "api_admin_document_get"
     * })
     */
    public function getOwner(): ?string
    {
        if ($this->getUpdatedBy() !== null) {
            return $this->getUpdatedBy()->getFirstName() . ' ' . $this->getUpdatedBy()->getLastName();
        }

        return null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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
     * @return mixed
     */
    public function getFacilities()
    {
        return $this->facilities;
    }

    /**
     * @param $facilities
     */
    public function setFacilities($facilities): void
    {
        $this->facilities = $facilities;
    }

    /**
     * @param Facility|null $facility
     */
    public function addFacility(?Facility $facility): void
    {
        $this->facilities->add($facility);
    }

    /**
     * @param Facility|null $facility
     */
    public function removeFacility(?Facility $facility): void
    {
        $this->facilities->removeElement($facility);
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param $roles
     */
    public function setRoles($roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param Role|null $role
     */
    public function addRole(?Role $role): void
    {
        $this->roles->add($role);
    }

    /**
     * @param Role|null $role
     */
    public function removeRole(?Role $role): void
    {
        $this->roles->removeElement($role);
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
