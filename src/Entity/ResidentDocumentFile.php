<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Class ResidentDocumentFile
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentDocumentFileRepository")
 * @ORM\Table(name="tbl_resident_document_file")
 */
class ResidentDocumentFile
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_document_file_list",
     *     "api_admin_resident_document_file_get",
     * })
     */
    private $id;

    /**
     * @var ResidentDocument
     * @Assert\NotNull(message = "Please select a Resident Document", groups={
     *     "api_admin_resident_document_file_add",
     *     "api_admin_resident_document_file_edit"
     * })
     * @ORM\OneToOne(targetEntity="App\Entity\ResidentDocument", inversedBy="file")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident_document", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_resident_document_file_list",
     *     "api_admin_resident_document_file_get"
     * })
     */
    private $residentDocument;

    /**
     * @var string $file
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_document_file_add",
     *     "api_admin_resident_document_file_edit"
     * })
     * @ORM\Column(name="file", type="blob")
     * @Groups({
     *     "api_admin_resident_document_file_list",
     *     "api_admin_resident_document_file_get",
     * })
     */
    private $file;

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
