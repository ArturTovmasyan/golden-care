<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class DocumentFile
 *
 * @ORM\Entity(repositoryClass="App\Repository\DocumentFileRepository")
 * @ORM\Table(name="tbl_document_file")
 */
class DocumentFile
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_document_file_list",
     *     "api_admin_document_file_get",
     *     "api_admin_document_list",
     *     "api_admin_document_get"
     * })
     */
    private $id;

    /**
     * @var Document
     * @Assert\NotNull(message = "Please select a Document", groups={
     *     "api_admin_document_file_add",
     *     "api_admin_document_file_edit"
     * })
     * @ORM\OneToOne(targetEntity="App\Entity\Document", inversedBy="file")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_document", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_document_file_list",
     *     "api_admin_document_file_get"
     * })
     */
    private $document;

    /**
     * @var string $file
     * @Assert\NotBlank(groups={
     *     "api_admin_document_file_add",
     *     "api_admin_document_file_edit"
     * })
     * @ORM\Column(name="file", type="blob")
     * @Groups({
     *     "api_admin_document_file_list",
     *     "api_admin_document_file_get",
     * })
     */
    private $file;

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("file")
     * @Serializer\Groups({"api_admin_document_get", "api_admin_document_list"})
     */
    public function getDocumentFile()
    {
        return stream_get_contents($this->getFile());
    }

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

    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param null|string $file
     */
    public function setFile(?string $file): void
    {
        $this->file = $file;
    }
}
