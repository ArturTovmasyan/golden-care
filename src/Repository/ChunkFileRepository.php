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
     * @param $userId
     * @return mixed
     */
    public function getChunkCount($requestId, $userId)
    {
        $qb = $this->createQueryBuilder('ch');
        $qb->select('COUNT(ch.id)')
            ->where('ch.requestId = :requestId')
            ->andWhere('ch.userId = :userId')
            ->setParameter('requestId', $requestId)
            ->setParameter('userId', $userId);

        return $qb->getQuery()->getSingleScalarResult();
    }
}