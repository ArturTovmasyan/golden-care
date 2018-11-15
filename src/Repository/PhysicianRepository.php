<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\PhysicianNotFoundException;
use App\Entity\CityStateZip;
use App\Entity\Physician;
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
     * @param Space $space
     */
    public function findBySpace(QueryBuilder $queryBuilder, Space $space)
    {
        $queryBuilder
            ->from(Physician::class, 'p')
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
}