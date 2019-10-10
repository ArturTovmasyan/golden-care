<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class ChunkFileRepository
 * @package App\Repository
 */
class ChunkFileRepository extends EntityRepository
{
    /**
     * This function is used to to return chunk count by request id
     *
     * @param $requestId
     * @param $userId
     * @return mixed
     */
    public function getChunkCount($requestId, $userId)
    {
        $qb = $this->createQueryBuilder('ch');
        $qb->select('ch.id')
            ->where('ch.requestId = :requestId AND ch.userId = :userId')
            ->groupBy('ch.chunkId')
            ->setParameter('requestId', $requestId)
            ->setParameter('userId', $userId);

        return \count($qb->getQuery()->getResult());
    }

    /**
     * This function is used to to return chunk string for base64 by request id
     *
     * @param $requestId
     * @param $userId
     * @return mixed
     */
    public function getChunks($requestId, $userId)
    {
        $qb = $this->createQueryBuilder('ch');
        $qb->select('ch.chunk')
            ->where('ch.requestId = :requestId AND ch.userId = :userId')
            ->orderBy('ch.chunkId')
            ->setParameter('requestId', $requestId)
            ->setParameter('userId', $userId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $requestId
     * @param $chunkId
     * @param $userId
     * @return int
     */
    public function getChunk($requestId, $chunkId, $userId)
    {
        $qb = $this->createQueryBuilder('ch');
        $qb->select('ch.id')
            ->where('ch.requestId = :requestId')
            ->andWhere('ch.chunkId = :chunkId AND ch.userId = :userId')
            ->orderBy('ch.chunkId')
            ->setParameter('requestId', $requestId)
            ->setParameter('chunkId', $chunkId)
            ->setParameter('userId', $userId);

        return \count($qb->getQuery()->getResult());
    }

    /**
     * This function is used to get chunks by request and user id
     *
     * @param $requestId
     * @param $userId
     * @return array
     */
    public function findChunkByRequestAndUserId($requestId, $userId)
    {
        $qb = $this
            ->createQueryBuilder('ch')
            ->select('ch')
            ->where('ch.requestId = :requestId AND ch.userId = :userId')
            ->setParameters(['requestId' => $requestId, 'userId' => $userId])
            ->getQuery()
            ->getResult();

        return $qb;
    }
}