<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Class ResidentResponsiblePerson
 *
 * @ORM\Entity(repositoryClass="App\Repository\ResidentResponsiblePersonRepository")
 * @ORM\Table(name="tbl_resident_responsible_person")
 * @UniqueEntity(
 *     fields={"resident", "responsiblePerson"},
 *     errorPath="responsible_person_id",
 *     message="The value is already in use for this Resident.",
 *     groups={
 *          "api_admin_resident_responsible_person_add",
 *          "api_admin_resident_responsible_person_edit"
 *     }
 * )
 */
class ResidentResponsiblePerson
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get",
     *     "api_admin_resident_private_pay_payment_received_item_list",
     *     "api_admin_resident_private_pay_payment_received_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="sort_order", type="integer", nullable=false, options={"default" = 0})
     */
    private $sortOrder;

    /**
     * @var Resident
     * @ORM\ManyToOne(targetEntity="App\Entity\Resident", inversedBy="residentResponsiblePersons")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_resident", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Resident",
     *      groups={
     *          "api_admin_resident_responsible_person_add",
     *          "api_admin_resident_responsible_person_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    private $resident;

    /**
     * @var ResponsiblePerson
     * @ORM\ManyToOne(targetEntity="App\Entity\ResponsiblePerson", inversedBy="residentResponsiblePersons", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_responsible_person", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotNull(
     *      message = "Please select a Responsible Person",
     *      groups={
     *          "api_admin_resident_responsible_person_add",
     *          "api_admin_resident_responsible_person_edit"
     *      }
     * )
     * @Assert\Valid(
     *      groups={
     *          "api_admin_resident_responsible_person_add",
     *          "api_admin_resident_responsible_person_edit"
     *      }
     * )
     * @Groups({
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get",
     *     "api_admin_resident_private_pay_payment_received_item_list",
     *     "api_admin_resident_private_pay_payment_received_item_get",
     *     "api_admin_resident_ledger_get"
     * })
     */
    private $responsiblePerson;

    /**
     * @var Relationship
     * @ORM\ManyToOne(targetEntity="Relationship", inversedBy="residentResponsiblePersons", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_relationship", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_responsible_person_add",
     *     "api_admin_resident_responsible_person_edit"
     * })
     * @Groups({
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    private $relationship;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="ResponsiblePersonRole", mappedBy="residentResponsiblePersons", cascade={"persist"})
     * @Groups({
     *     "api_admin_resident_responsible_person_list",
     *     "api_admin_resident_responsible_person_get"
     * })
     */
    protected $roles;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Entity\ResidentPrivatePayPaymentReceivedItem", mappedBy="responsiblePerson", cascade={"remove", "persist"})
     */
    private $residentPrivatePayPaymentReceivedItems;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(?int $id): void
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

    /**
     * @return ResponsiblePerson|null
     */
    public function getResponsiblePerson(): ?ResponsiblePerson
    {
        return $this->responsiblePerson;
    }

    /**
     * @param ResponsiblePerson|null $responsiblePerson
     */
    public function setResponsiblePerson(?ResponsiblePerson $responsiblePerson): void
    {
        $this->responsiblePerson = $responsiblePerson;
    }

    /**
     * @return Relationship|null
     */
    public function getRelationship(): ?Relationship
    {
        return $this->relationship;
    }

    /**
     * @param Relationship|null $relationship
     */
    public function setRelationship(?Relationship $relationship): void
    {
        $this->relationship = $relationship;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles): void
    {
        $this->roles = $roles;

        /** @var ResponsiblePersonRole $role */
        foreach ($this->roles as $role) {
            $role->addResidentResponsiblePerson($this);
        }
    }

    /**
     * @param ResponsiblePersonRole $role
     */
    public function addRole(ResponsiblePersonRole $role): void
    {
        $role->addResidentResponsiblePerson($this);
        $this->roles[] = $role;
    }

    /**
     * @param ResponsiblePersonRole $role
     */
    public function removeRole(ResponsiblePersonRole $role): void
    {
        $this->roles->removeElement($role);
        $role->removeResidentResponsiblePerson($this);
    }

    /**
     * @return int
     */
    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     */
    public function setSortOrder(?int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * @return ArrayCollection
     */
    public function getResidentPrivatePayPaymentReceivedItems(): ArrayCollection
    {
        return $this->residentPrivatePayPaymentReceivedItems;
    }

    /**
     * @param ArrayCollection $residentPrivatePayPaymentReceivedItems
     */
    public function setResidentPrivatePayPaymentReceivedItems(ArrayCollection $residentPrivatePayPaymentReceivedItems): void
    {
        $this->residentPrivatePayPaymentReceivedItems = $residentPrivatePayPaymentReceivedItems;
    }
}
