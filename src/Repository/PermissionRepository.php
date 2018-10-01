<?php

namespace App\Repository;

use App\Entity\Permission;
use App\Entity\Space;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Class PermissionRepository
 * @package App\Repository
 */
class PermissionRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param Space|null $space
     * @return mixed
     */
    public function getUserPermissions(User $user, Space $space = null)
    {
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(Permission::class , 'p');
        $rsm->addFieldResult('p', 'id', 'id');
        $rsm->addFieldResult('p', 'name', 'name');

        if (is_null($space)) {
            $condition = "sur.space_id IS NULL AND sur.user_id = :user_id";
        } else {
            $condition = "(sur.space_id = :space_id OR sur.space_id IS NULL) AND sur.user_id = :user_id";
        }

        $sql = "SELECT p.id, p.name  
                FROM permission p   
                  INNER JOIN role_permission rp ON rp.permission_id = p.id
                  INNER JOIN space_user_role sur ON sur.role_id = rp.role_id
                WHERE " . $condition;

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameter('user_id', $user->getId());

        if (!is_null($space)) {
            $query->setParameter('space_id', $space->getId());
        }

        return $query->getResult();
    }
}