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
     * @ORM\Column(name="compatibility", type="integer", nullable=true)
     * @Groups({
     *      "api_admin_user_get",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_physician_list",
     *      "api_admin_physician_get",
     *      "api_admin_resident_physician_list",
     *      "api_admin_responsible_person_list",
     *      "api_admin_responsible_person_get",
     *      "api_admin_resident_responsible_person_list",
     *      "api_profile_me",
     *      "api_lead_organization_list",
     *      "api_lead_organization_get",
     *      "api_lead_referral_list",
     *      "api_lead_referral_get",
     *      "api_lead_lead_get"
     * })
     */
    private $compatibility;

    /**
     * @ORM\Column(name="type", type="smallint", nullable=false)
     * @Assert\Choice(
     *      callback={"App\Model\Phone","getTypeValues"},
     *      groups={
     *          "api_admin_user_add",
     *          "api_admin_user_edit",
     *          "api_admin_resident_add",
     *          "api_admin_resident_edit",
     *          "api_admin_responsible_person_add",
     *          "api_admin_responsible_person_edit",
     *          "api_profile_edit",
     *          "api_lead_organization_add",
     *          "api_lead_organization_edit",
     *          "api_lead_referral_organization_required_add",
     *          "api_lead_referral_organization_required_edit"
     *      }
     * )
     * @Groups({
     *      "api_admin_user_get",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_physician_list",
     *      "api_admin_physician_get",
     *      "api_admin_resident_physician_list",
     *      "api_admin_responsible_person_list",
     *      "api_admin_responsible_person_get",
     *      "api_admin_resident_responsible_person_list",
     *      "api_profile_me",
     *      "api_lead_organization_list",
     *      "api_lead_organization_get",
     *      "api_lead_referral_list",
     *      "api_lead_referral_get",
     *      "api_lead_lead_get"
     * })
     */
    private $type;

    /**
     * @ORM\Column(name="number", type="string", nullable=false, length=50)
     * @Assert\Regex(
     *     pattern="/^\([0-9]{3}\)\s?[0-9]{3}-[0-9]{4}$/",
     *     message="Invalid phone number format. Valid format is (XXX) XXX-XXXX.",
     *     groups={
     *         "api_admin_user_add",
     *         "api_admin_user_edit",
     *         "api_admin_resident_add",
     *         "api_admin_resident_edit",
     *         "api_admin_responsible_person_add",
     *         "api_admin_responsible_person_edit",
     *         "api_profile_edit",
     *         "api_lead_organization_add",
     *         "api_lead_organization_edit",
     *         "api_lead_referral_organization_required_add",
     *         "api_lead_referral_organization_required_edit"
     * })
     * @Groups({
     *      "api_admin_user_get",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_physician_list",
     *      "api_admin_physician_get",
     *      "api_admin_resident_physician_list",
     *      "api_admin_responsible_person_list",
     *      "api_admin_responsible_person_get",
     *      "api_admin_resident_responsible_person_list",
     *      "api_profile_me",
     *      "api_lead_organization_list",
     *      "api_lead_organization_get",
     *      "api_lead_referral_list",
     *      "api_lead_referral_get",
     *      "api_lead_lead_get"
     * })
     */
    private $number;

    /**
     * @var bool
     * @ORM\Column(name="is_primary", type="boolean", nullable=false)
     * @Assert\NotNull(groups={
     *      "api_admin_user_add",
     *      "api_admin_user_edit",
     *      "api_admin_resident_add",
     *      "api_admin_resident_edit",
     *      "api_admin_responsible_person_add",
     *      "api_admin_responsible_person_edit",
     *      "api_profile_edit",
     *      "api_lead_organization_add",
     *      "api_lead_organization_edit",
     *      "api_lead_referral_organization_required_add",
     *      "api_lead_referral_organization_required_edit"
     * })
     * @Groups({
     *      "api_admin_user_get",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_physician_list",
     *      "api_admin_physician_get",
     *      "api_admin_resident_physician_list",
     *      "api_admin_responsible_person_list",
     *      "api_admin_responsible_person_get",
     *      "api_admin_resident_responsible_person_list",
     *      "api_profile_me",
     *      "api_lead_organization_list",
     *      "api_lead_organization_get",
     *      "api_lead_referral_list",
     *      "api_lead_referral_get",
     *      "api_lead_lead_get"
     * })
     */
    private $primary = false;

    /**
     * @var bool
     * @ORM\Column(name="is_sms_enabled", type="boolean", nullable=false)
     * @Groups({
     *      "api_admin_user_get",
     *      "api_admin_resident_list",
     *      "api_admin_resident_get",
     *      "api_admin_physician_list",
     *      "api_admin_physician_get",
     *      "api_admin_resident_physician_list",
     *      "api_admin_responsible_person_list",
     *      "api_admin_responsible_person_get",
     *      "api_admin_resident_responsible_person_list",
     *      "api_profile_me"
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
