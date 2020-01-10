<?php
namespace App\Api\V1\Common\Service;

use Doctrine\ORM\QueryBuilder;


interface IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return mixed
     */
    public function gridSelect(QueryBuilder $queryBuilder, $params);

    /**
     * @param $params
     * @return mixed
     */
    public function list($params);
}