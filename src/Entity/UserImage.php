<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use App\Model\Persistence\Entity\UserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
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
     * @Groups({
     *     "api_profile_edit",
     *     "api_profile_me"
     * })
     */
    private $id;

    /**
     * @var User
     * @Assert\NotNull(message = "Please select an User", groups={
     *     "api_admin_user_image_add",
     *     "api_admin_user_image_edit"
     * })
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="images")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_user", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *     "api_profile_edit",
     *     "api_profile_me"
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
     * @Groups({
     *     "api_profile_edit",
     *     "api_profile_me"
     * })
     */
    private $photo;

    /**
     * @var string $title
     * @ORM\Column(name="title", type="string")
     * @Assert\NotBlank(groups={
     *     "api_admin_user_image_add",
     *     "api_admin_user_image_edit"
     * })
     * @Groups({
     *     "api_profile_edit",
     *     "api_profile_me"
     * })
     */
    private $title;

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
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}
