<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use DataURI\Data;
use DataURI\Dumper;
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
     *     "api_admin_resident_resident_document_add",
     *     "api_admin_resident_resident_document_edit"
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
     * @var ResidentDocumentFile
     * @ORM\OneToOne(targetEntity="App\Entity\ResidentDocumentFile", mappedBy="residentDocument", cascade={"remove", "persist"})
     */
    private $file;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("file")
     * @Serializer\Groups({"api_admin_resident_document_get", "api_admin_resident_document_list"})
     */
    public function getResidentDocumentFile()
    {
        if ($this->getFile() !== null) {
            $data = stream_get_contents($this->getFile()->getFile());
            $file_info = new \finfo(FILEINFO_MIME_TYPE);

            return Dumper::dump(new Data($data, $file_info->buffer($data)));
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
     * @return ResidentDocumentFile|null
     */
    public function getFile(): ?ResidentDocumentFile
    {
        return $this->file;
    }

    /**
     * @param ResidentDocumentFile|null $file
     */
    public function setFile(?ResidentDocumentFile $file): void
    {
        $this->file = $file;
    }
}
