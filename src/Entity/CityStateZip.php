<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use App\Annotation\Grid;

/**
 * Class CityStateZip
 *
 * @ORM\Entity(repositoryClass="App\Repository\CityStateZipRepository")
 * @ORM\Table(name="tbl_city_state_zip")
 * @Grid(
 *     api_admin_city_state_zip_grid={
 *          {"id", "number", true, true, "csz.id"},
 *          {"stateFull", "string", true, true, "csz.stateFull"},
 *          {"state2Ltr", "string", true, true, "csz.state2Ltr"},
 *          {"zipMain", "string", true, true, "csz.zipMain"},
 *          {"zipSub", "string", true, true, "csz.zipSub"},
 *          {"city", "string", true, true, "csz.city"},
 *     }
 * )
 */
class CityStateZip
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_admin_city_state_zip_grid", "api_admin_city_state_zip_list", "api_admin_city_state_zip_get"})
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_city_state_zip_add", "api_admin_city_state_zip_edit"})
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "State Full cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_city_state_zip_add", "api_admin_city_state_zip_edit"}
     * )
     * @ORM\Column(name="state_full", type="string", length=100)
     * @Groups({"api_admin_city_state_zip_grid", "api_admin_city_state_zip_list", "api_admin_city_state_zip_get"})
     */
    private $stateFull;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_city_state_zip_add", "api_admin_city_state_zip_edit"})
     * @Assert\Regex(
     *     pattern="/\b([A-Z]{2})\b/",
     *     message="Invalid State abbreviation.",
     *     groups={"api_admin_city_state_zip_add", "api_admin_city_state_zip_edit"}
     * )
     * @ORM\Column(name="state_2_ltr", type="string", length=2)
     * @Groups({"api_admin_city_state_zip_grid", "api_admin_city_state_zip_list", "api_admin_city_state_zip_get"})
     */
    private $state2Ltr;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_city_state_zip_add", "api_admin_city_state_zip_edit"})
     * @Assert\Regex(
     *     pattern="/^[0-9]{5}([- ]?[0-9]{4})?$/",
     *     message="Invalid ZIP code.",
     *     groups={"api_admin_city_state_zip_add", "api_admin_city_state_zip_edit"}
     * )
     * @ORM\Column(name="zip_main", type="string", length=10)
     * @Groups({"api_admin_city_state_zip_grid", "api_admin_city_state_zip_list", "api_admin_city_state_zip_get"})
     */
    private $zipMain;

    /**
     * @var string $zipSub
     * @ORM\Column(name="zip_sub", type="string", length=10, nullable=true)
     * @Assert\Length(
     *      max = 10,
     *      maxMessage = "ZIP Sub cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_city_state_zip_add", "api_admin_city_state_zip_edit"}
     * )
     * @Groups({"api_admin_city_state_zip_grid", "api_admin_city_state_zip_list", "api_admin_city_state_zip_get"})
     */
    private $zipSub;

    /**
     * @var string
     * @Assert\NotBlank(groups={"api_admin_city_state_zip_add", "api_admin_city_state_zip_edit"})
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "City cannot be longer than {{ limit }} characters",
     *      groups={"api_admin_city_state_zip_add", "api_admin_city_state_zip_edit"}
     * )
     * @ORM\Column(name="city", type="string", length=100)
     * @Groups({"api_admin_city_state_zip_grid", "api_admin_city_state_zip_list", "api_admin_city_state_zip_get"})
     */
    private $city;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getStateFull(): ?string
    {
        return $this->stateFull;
    }

    public function setStateFull(?string $stateFull)
    {
        $this->stateFull = $stateFull;
    }

    public function getState2Ltr(): ?string
    {
        return $this->state2Ltr;
    }

    public function setState2Ltr(?string $state2Ltr)
    {
        $this->state2Ltr = $state2Ltr;
    }

    public function getZipMain(): ?string
    {
        return $this->zipMain;
    }

    public function setZipMain(?string $zipMain)
    {
        $this->zipMain = $zipMain;
    }

    public function getZipSub(): ?string
    {
        return $this->zipSub;
    }

    public function setZipSub(?string $zipSub)
    {
        $this->zipSub = $zipSub;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city)
    {
        $this->city = $city;
    }
}
