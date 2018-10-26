<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\SpaceHaventDefaultRoleException;
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
        try {
            if (is_null($space)) {
                $query = $this->createQueryBuilder('r')
                    ->where('r.spaceDefault = :spaceDefault')
                    ->setParameter('spaceDefault', true);
            } else {
                $query = $this->createQueryBuilder('r')
                    ->where('(r.spaceDefault = :spaceDefault AND r.space = :space) OR (r.spaceDefault = :spaceDefault AND r.space IS NULL)')
                    ->setParameter('spaceDefault', true)
                    ->setParameter('space', $space);
            }

            return $query->getQuery()->getOneOrNullResult();
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException $e) {
            throw new SpaceHaventDefaultRoleException();
        }
    }

    /**
     * @param $space
     * @return mixed
     */
    public function findRolesBySpace(Space $space)
    {
        return $this->createQueryBuilder('r')
            ->where('r.space = :space OR (r.default = :default AND r.space IS NULL)')
            ->setParameter('space', $space)
            ->setParameter('default', true)
            ->groupBy('r.id')
            ->getQuery()
            ->getResult();
    }
}