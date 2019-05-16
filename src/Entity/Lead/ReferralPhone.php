<?php
namespace App\Entity\Lead;

use App\Model\Persistence\Entity\PhoneTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Annotation\Grid as Grid;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="tbl_lead_referral_phone")
 * @ORM\Entity(repositoryClass="App\Repository\Lead\ReferralPhoneRepository")
 */
class ReferralPhone
{
    use PhoneTrait;

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Groups({
     *      "api_lead_referral_list",
     *      "api_lead_referral_get",
     * })
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lead\Referral", inversedBy="phones", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_referral", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Assert\NotBlank(groups={
     *      "api_lead_referral_organization_required_add",
     *      "api_lead_referral_organization_required_edit"
     * })
     */
    private $referral;

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
     * @return mixed
     */
    public function getReferral()
    {
        return $this->referral;
    }

    /**
     * @param $referral
     */
    public function setReferral($referral): void
    {
        $this->referral = $referral;
    }
}
