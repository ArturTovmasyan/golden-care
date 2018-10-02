<?php

namespace App\Repository;

use App\Entity\Space;
use Doctrine\ORM\EntityRepository;

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
}