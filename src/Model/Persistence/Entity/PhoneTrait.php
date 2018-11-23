<?php

namespace App\Model\Persistence\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Trait PhoneTrait
 * @package AppBundle\Model\Persistence\Entity
 */
trait PhoneTrait
{
    /**
     * @ORM\Column(name="compatibility", type="integer", nullable=false)
     * @Assert\Choice(
     *      callback={"App\Model\Phone","getCompatibleValues"},
     *      groups={
     *          "api_admin_resident_add",
     *          "api_admin_resident_edit"
     *      }
     * )
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $compatibility;

    /**
     * @ORM\Column(name="type", type="smallint", nullable=false)
     * @Assert\Choice(
     *      callback={"App\Model\Phone","getTypeValues"},
     *      groups={
     *          "api_admin_resident_add",
     *          "api_admin_resident_edit"
     *      }
     * )
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $type;

    /**
     * @ORM\Column(name="number", type="string", nullable=false, length=50)
     * @Assert\NotBlank(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $number;

    /**
     * @var bool
     * @ORM\Column(name="is_primary", type="boolean", nullable=false)
     * @Assert\NotNull(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $primary = false;

    /**
     * @var bool
     * @ORM\Column(name="is_sms_enabled", type="boolean", nullable=false)
     * @Assert\NotNull(groups={
     *     "api_admin_resident_add",
     *     "api_admin_resident_edit"
     * })
     * @Groups({
     *      "api_admin_resident_list",
     *      "api_admin_resident_get"
     * })
     */
    private $smsEnabled = false;

    /**
     * @return mixed
     */
    public function getCompatibility()
    {
        return $this->compatibility;
    }

    /**
     * @param mixed $compatibility
     */
    public function setCompatibility($compatibility): void
    {
        $this->compatibility = $compatibility;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number): void
    {
        $this->number = $number;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     */
    public function setExtension($extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->primary;
    }

    /**
     * @param bool $primary
     */
    public function setPrimary(bool $primary): void
    {
        $this->primary = $primary;
    }

    /**
     * @return bool
     */
    public function isSmsEnabled(): bool
    {
        return $this->smsEnabled;
    }

    /**
     * @param bool $smsEnabled
     */
    public function setSmsEnabled(bool $smsEnabled): void
    {
        $this->smsEnabled = $smsEnabled;
    }
}