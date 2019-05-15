<?php

namespace App\Entity\Lead;

use App\Entity\Space;
use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class ReferrerType
 *
 * @ORM\Entity(repositoryClass="App\Repository\Lead\ReferrerTypeRepository")
 * @UniqueEntity(
 *     fields={"space", "title"},
 *     errorPath="title",
 *     message="This title is already in use on that space.",
 *     groups={
 *          "api_lead_referrer_type_add",
 *          "api_lead_referrer_type_edit"
 *     }
 * )
 * @ORM\Table(name="tbl_lead_referrer_type")
 * @Grid(
 *     api_lead_referrer_type_grid={
 *          {
 *              "id"         = "id",
 *              "type"       = "id",
 *              "hidden"     = true,
 *              "field"      = "rt.id"
 *          },
 *          {
 *              "id"         = "title",
 *              "type"       = "string",
 *              "field"      = "rt.title",
 *              "link"       = ":edit"
 *          },
 *          {
 *              "id"         = "organization_required",
 *              "type"       = "boolean",
 *              "field"      = "rt.organizationRequired"
 *          },
 *          {
 *              "id"         = "representative_required",
 *              "type"       = "boolean",
 *              "field"      = "rt.representativeRequired"
 *          },
 *          {
 *              "id"         = "space",
 *              "type"       = "string",
 *              "field"      = "s.name"
 *          }
 *     }
 * )
 */
class ReferrerType
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_lead_referrer_type_list",
     *     "api_lead_referrer_type_get"
     * })
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     groups={
     *          "api_lead_referrer_type_add",
     *          "api_lead_referrer_type_edit"
     *      }
     * )
     * @Assert\Length(
     *      max = 120,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_lead_referrer_type_add",
     *          "api_lead_referrer_type_edit"
     *      }
     * )
     * @ORM\Column(name="title", type="string", length=120)
     * @Groups({
     *     "api_lead_referrer_type_grid",
     *     "api_lead_referrer_type_list",
     *     "api_lead_referrer_type_get",
     *     "api_lead_organization_list",
     *     "api_lead_organization_get"
     * })
     */
    private $title;

    /**
     * @var bool
     * @ORM\Column(name="organization_required", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_lead_referrer_type_grid",
     *     "api_lead_referrer_type_list",
     *     "api_lead_referrer_type_get"
     * })
     */
    private $organizationRequired;

    /**
     * @var bool
     * @ORM\Column(name="representative_required", type="boolean", options={"default" = 0})
     * @Groups({
     *     "api_lead_referrer_type_grid",
     *     "api_lead_referrer_type_list",
     *     "api_lead_referrer_type_get"
     * })
     */
    private $representativeRequired;

    /**
     * @var Space
     * @Assert\NotNull(message = "Please select a Space", groups={
     *     "api_lead_referrer_type_add",
     *     "api_lead_referrer_type_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="leadReferrerTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_space", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_lead_referrer_type_grid",
     *     "api_lead_referrer_type_list",
     *     "api_lead_referrer_type_get"
     * })
     */
    private $space;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\Lead\Organization", mappedBy="category", cascade={"remove", "persist"})
     */
    private $organizations;

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
     * @return bool
     */
    public function isOrganizationRequired(): bool
    {
        return $this->organizationRequired;
    }

    /**
     * @param bool $organizationRequired
     */
    public function setOrganizationRequired(bool $organizationRequired): void
    {
        $this->organizationRequired = $organizationRequired;
    }

    /**
     * @return bool
     */
    public function isRepresentativeRequired(): bool
    {
        return $this->representativeRequired;
    }

    /**
     * @param bool $representativeRequired
     */
    public function setRepresentativeRequired(bool $representativeRequired): void
    {
        $this->representativeRequired = $representativeRequired;
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
    public function getOrganizations(): ArrayCollection
    {
        return $this->organizations;
    }

    /**
     * @param ArrayCollection $organizations
     */
    public function setOrganizations(ArrayCollection $organizations): void
    {
        $this->organizations = $organizations;
    }
}
