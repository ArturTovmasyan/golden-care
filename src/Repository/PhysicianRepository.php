<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Entity\CityStateZip;
use App\Entity\Physician;
use App\Entity\Salutation;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Class PhysicianRepository
 * @package App\Repository
 */
class PhysicianRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function search(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->from(Physician::class, 'p')
            ->leftJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'sal = p.salutation'
            )
            ->leftJoin(
                Space::class,
                's',
                Join::WITH,
                's = p.space'
            )
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'csz = p.csz'
            )
            ->groupBy('p.id');
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Space $space
     */
    public function searchBySpace(QueryBuilder $queryBuilder, Space $space)
    {
        $queryBuilder
            ->from(Physician::class, 'p')
            ->leftJoin(
                Salutation::class,
                'sal',
                Join::WITH,
                'sal = p.salutation'
            )
            ->leftJoin(
                CityStateZip::class,
                'csz',
                Join::WITH,
                'csz = p.csz'
            )
            ->where('p.space = :space')
            ->setParameter('space', $space)
            ->groupBy('p.id');
    }

    /**
     * @param Space $space
     * @param $id
     * @return mixed
     */
    public function findBySpaceAndId(Space $space, $id)
    {
        try {
            return $this->createQueryBuilder('p')
                ->where('p.space = :space AND p.id=:id')
                ->setParameter('space', $space)
                ->setParameter('id', $id)
                ->groupBy('p.id')
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException $e) {
            throw new PhysicianNotFoundException();
        }
    }

    /**
     * @param Space $space
     * @param $id
     * @return mixed
     */
    public function findBySpace(Space $space)
    {
        try {
            return $this->createQueryBuilder('p')
                ->where('p.space = :space')
                ->setParameter('space', $space)
                ->groupBy('p.id')
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException $e) {
            throw new PhysicianNotFoundException();
        }
    }

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb->where($qb->expr()->in('p.id', $ids))
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $ids
     * @param Space $space
     * @return mixed
     */
    public function findByIdsAndSpace($ids, Space $space)
    {
        $qb = $this->createQueryBuilder('p');

        return $qb->where($qb->expr()->in('p.id', $ids))
            ->andWhere('p.space = :space')
            ->setParameter('space', $space)
            ->groupBy('p.id')
            ->getQuery()
            ->getResult();
    }
}