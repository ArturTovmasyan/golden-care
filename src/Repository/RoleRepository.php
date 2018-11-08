<?php

namespace App\Repository;

use App\Api\V1\Common\Service\Exception\RoleNotFoundException;
use App\Api\V1\Common\Service\Exception\SpaceHaventDefaultRoleException;
use App\Entity\Role;
use App\Entity\Space;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class RoleRepository
 * @package App\Repository
 */
class RoleRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @return Paginator
     */
    public function searchAllRoles(QueryBuilder $queryBuilder)
    {
        return new Paginator(
            $queryBuilder
                ->select('r')
                ->from(Role::class, 'r')
                ->groupBy('r.id')
                ->getQuery()
        );
    }

    /**
     * @param Space|null $space
     * @return mixed
     */
    public function getSpaceDefaultRole(Space $space = null)
    {
        try {
            if (is_null($space)) {
                $query = $this->createQueryBuilder('r')
                    ->where('r.spaceDefault = :spaceDefault')
                    ->setParameter('spaceDefault', 1);
            } else {
                $query = $this->createQueryBuilder('r')
                    ->where('(r.spaceDefault = :spaceDefault AND r.space = :space) OR (r.spaceDefault = :spaceDefault AND r.space IS NULL)')
                    ->setParameter('spaceDefault', 1)
                    ->setParameter('space', $space);
            }

            return $query->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException $e) {
            throw new SpaceHaventDefaultRoleException();
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param Space $space
     * @return Paginator
     */
    public function findRolesBySpace(QueryBuilder $queryBuilder, Space $space)
    {
        return new Paginator(
            $queryBuilder
                ->select('r')
                ->from(Role::class, 'r')
                ->where('r.space = :space OR (r.default = :default AND r.space IS NULL)')
                ->setParameter('space', $space)
                ->setParameter('default', true)
                ->groupBy('r.id')
                ->getQuery()
        );
    }

    /**
     * @param Space $space
     * @param $id
     * @return mixed
     */
    public function findRolesBySpaceAndId(Space $space, $id)
    {
        try {
            return $this->createQueryBuilder('r')
                ->where('(r.space = :space AND r.id=:id) OR (r.default = :default AND r.space IS NULL AND r.id=:id)')
                ->setParameter('space', $space)
                ->setParameter('default', true)
                ->setParameter('id', $id)
                ->groupBy('r.id')
                ->getQuery()
                ->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException $e) {
            throw new RoleNotFoundException();
        }
    }
}