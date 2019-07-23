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
 * Class Document
 *
 * @ORM\Entity(repositoryClass="App\Repository\DocumentRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_document_add",
 *          "api_admin_document_edit"
 *     }
 * )
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
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "d.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "description",
 *              "type"       = "string",
 *              "field"      = "d.description"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
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
     *     "api_admin_document_get",
     *     "api_admin_health_insurance_list",
     *     "api_admin_health_insurance_get"
     * })
     */
    private $id;

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
     *     "api_admin_document_get",
     *     "api_admin_health_insurance_list",
     *     "api_admin_health_insurance_get"
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
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_document_add",
     *     "api_admin_document_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="documents")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_document_list",
     *     "api_admin_document_get"
     * })
     */
    private $space;

    /**
     * @var DocumentFile
     * @ORM\OneToOne(targetEntity="App\Entity\DocumentFile", mappedBy="document", cascade={"remove", "persist"})
     * @Groups({
     *     "api_admin_document_list",
     *     "api_admin_document_get"
     * })
     */
    private $file;

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
     * @return DocumentFile|null
     */
    public function getFile(): ?DocumentFile
    {
        return $this->file;
    }

    /**
     * @param DocumentFile|null $file
     */
    public function setFile(?DocumentFile $file): void
    {
        $this->file = $file;
    }

}
