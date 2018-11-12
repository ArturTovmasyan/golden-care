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
            $condition = "sur.id_space IS NULL AND sur.id_user = :user_id";
        } else {
            $condition = "(sur.id_space = :space_id OR sur.id_space IS NULL) AND sur.id_user = :user_id";
        }

        // TODO(harutg): review
        $sql = "SELECT p.id, p.name  
                FROM tbl_permission p   
                  INNER JOIN tbl_role_permission rp ON rp.id_permission = p.id
                  INNER JOIN tbl_space_user_role sur ON sur.id_role = rp.id_role
                WHERE " . $condition;

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameter('user_id', $user->getId());

        if (!is_null($space)) {
            $query->setParameter('space_id', $space->getId());
        }

        return $query->getResult();
    }
}