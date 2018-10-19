<?php

namespace App\Repository;

use App\Entity\Space;
use App\Entity\SpaceUserRole;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class RoleRepository
 * @package App\Repository
 */
class RoleRepository extends EntityRepository
{
    /**
     * @param Space|null $space
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSpaceDefaultRole(Space $space = null)
    {
        if (is_null($space)) {
            $query = $this->createQueryBuilder('r')
                ->where('r.spaceDefault = :spaceDefault')
                ->setParameter('spaceDefault', true);
        } else {
            $query = $this->createQueryBuilder('r')
                ->where('r.spaceDefault = :spaceDefault AND r.space = :space')
                ->setParameter('spaceDefault', true)
                ->setParameter('space', $space);
        }

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $space
     * @return mixed
     */
    public function findRolesBySpace(Space $space)
    {
        return $this->createQueryBuilder('r')
            ->innerJoin(
                SpaceUserRole::class,
                'sur',
                Join::WITH,
                'sur.role = r'
            )
            ->where('sur.space = :space')
            ->setParameter('space', $space)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }
}