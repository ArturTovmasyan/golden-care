<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation as Serializer;
use App\Annotation\Grid;

/**
 * Class UserImage
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserImageRepository")
 * @ORM\Table(name="tbl_user_image")
 */
class UserImage
{
    use TimeAwareTrait;
    use UserAwareTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     * @Assert\NotNull(message = "Please select an User", groups={
     *     "api_admin_user_image_add",
     *     "api_admin_user_image_edit"
     * })
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $user;

    /**
     * @var string $photo
     * @ORM\Column(name="photo", type="text")
     * @Assert\NotBlank(groups={
     *     "api_admin_user_image_add",
     *     "api_admin_user_image_edit"
     * })
     */
    private $photo;


    /**
     * @var string $photo
     * @ORM\Column(name="photo_35_35", type="text")
     * @Assert\NotBlank(groups={
     *     "api_admin_user_image_add",
     *     "api_admin_user_image_edit"
     * })
     * @Groups({
     *     "api_profile_me"
     * })
     */
    private $photo_35_35;

    /**
     * @var string $photo
     * @ORM\Column(name="photo_150_150", type="text")
     * @Assert\NotBlank(groups={
     *     "api_admin_user_image_add",
     *     "api_admin_user_image_edit"
     * })
     * @Groups({
     *     "api_profile_view"
     * })
     */
    private $photo_150_150;

    /**
     * @var string $photo
     * @ORM\Column(name="photo_300_300", type="text")
     * @Assert\NotBlank(groups={
     *     "api_admin_user_image_add",
     *     "api_admin_user_image_edit"
     * })
     */
    private $photo_300_300;

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
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return null|string
     */
    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    /**
     * @param null|string $photo
     */
    public function setPhoto(?string $photo): void
    {
        $this->photo = $photo;
    }

    /**
     * @return null|string
     */
    public function getPhoto3535(): ?string
    {
        return $this->photo_35_35;
    }

    /**
     * @param null|string $photo_35_35
     */
    public function setPhoto3535(?string $photo_35_35): void
    {
        $this->photo_35_35 = $photo_35_35;
    }

    /**
     * @return null|string
     */
    public function getPhoto150150(): ?string
    {
        return $this->photo_150_150;
    }

    /**
     * @param null|string $photo_150_150
     */
    public function setPhoto150150(?string $photo_150_150): void
    {
        $this->photo_150_150 = $photo_150_150;
    }

    /**
     * @return null|string
     */
    public function getPhoto300300(): ?string
    {
        return $this->photo_300_300;
    }

    /**
     * @param null|string $photo_300_300
     */
    public function setPhoto300300(?string $photo_300_300): void
    {
        $this->photo_300_300 = $photo_300_300;
    }
}
