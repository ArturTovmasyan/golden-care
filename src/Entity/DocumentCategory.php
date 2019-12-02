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
 * Class DocumentCategory
 *
 * @ORM\Entity(repositoryClass="App\Repository\DocumentCategoryRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="The title is already in use in this space.",
 *     groups={
 *          "api_admin_document_category_add",
 *          "api_admin_document_category_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_document_category")
 * @Grid(
 *     api_admin_document_category_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "dc.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "dc.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class DocumentCategory
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_document_category_list",
     *     "api_admin_document_category_get",
     *     "api_admin_document_list",
     *     "api_admin_document_get",
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={
     *     "api_admin_document_category_add",
     *     "api_admin_document_category_edit"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *           "api_admin_document_category_add",
     *           "api_admin_document_category_edit"
     * })
     * @ORM\Column(name="title", type="string", length=255)
     * @Groups({
     *     "api_admin_document_category_list",
     *     "api_admin_document_category_get",
     *     "api_admin_document_list",
     *     "api_admin_document_get",
     *     "api_admin_facility_document_list",
     *     "api_admin_facility_document_get"
     * })
     */
    private $title;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_admin_document_category_add",
     *     "api_admin_document_category_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="documentCategories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_admin_document_category_list",
     *     "api_admin_document_category_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Document", mappedBy="category", cascade={"remove", "persist"})
     */
    private $documents;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\FacilityDocument", mappedBy="category", cascade={"remove", "persist"})
     */
    private $facilityDocuments;

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
     * @return ArrayCollection
     */
    public function getDocuments(): ArrayCollection
    {
        return $this->documents;
    }

    /**
     * @param ArrayCollection $documents
     */
    public function setDocuments(ArrayCollection $documents): void
    {
        $this->documents = $documents;
    }

    /**
     * @return ArrayCollection
     */
    public function getFacilityDocuments(): ArrayCollection
    {
        return $this->facilityDocuments;
    }

    /**
     * @param ArrayCollection $facilityDocuments
     */
    public function setFacilityDocuments(ArrayCollection $facilityDocuments): void
    {
        $this->facilityDocuments = $facilityDocuments;
    }
}
