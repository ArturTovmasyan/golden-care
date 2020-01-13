<?php

namespace App\Entity;

use App\Model\Persistence\Entity\TimeAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ChunkFile
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\ChunkFileRepository")
 */
class ChunkFile
{
    use TimeAwareTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="request_id", type="string", length=255)
     * @Assert\NotBlank(groups={
     *     "api_admin_chunk_file_add"
     * })
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Request Id cannot be longer than {{ limit }} characters",
     *      groups={
     *          "api_admin_chunk_file_add"
     * })
     */
    private $requestId;

    /**
     * @var string
     *
     * @ORM\Column(name="chunk", type="text")
     * @Assert\NotBlank(groups={
     *     "api_admin_chunk_file_add"
     * })
     */
    private $chunk;

    /**
     * @var integer
     *
     * @ORM\Column(name="chunk_id", type="integer")
     * @Assert\NotBlank(groups={
     *     "api_admin_chunk_file_add"
     * })
     */
    private $chunkId;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     * @Assert\NotBlank(groups={
     *     "api_admin_chunk_file_add"
     * })
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_chunk", type="integer")
     * @Assert\NotBlank(groups={
     *     "api_admin_chunk_file_add"
     * })
     */
    private $totalChunk;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * @param null|string $requestId
     */
    public function setRequestId(?string $requestId): void
    {
        $this->requestId = $requestId;
    }

    /**
     * @return string
     */
    public function getChunk(): string
    {
        return $this->chunk;
    }

    /**
     * @param string $chunk
     */
    public function setChunk(string $chunk): void
    {
        $this->chunk = $chunk;
    }

    /**
     * @return int|null
     */
    public function getChunkId(): ?int
    {
        return $this->chunkId;
    }

    /**
     * @param int|null $chunkId
     */
    public function setChunkId(?int $chunkId): void
    {
        $this->chunkId = $chunkId;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int|null $userId
     */
    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return int|null
     */
    public function getTotalChunk(): ?int
    {
        return $this->totalChunk;
    }

    /**
     * @param int|null $totalChunk
     */
    public function setTotalChunk(?int $totalChunk): void
    {
        $this->totalChunk = $totalChunk;
    }
}

