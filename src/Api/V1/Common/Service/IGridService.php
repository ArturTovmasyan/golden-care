<?php
namespace App\Api\V1\Common\Service;

use Doctrine\ORM\QueryBuilder;


interface IGridService
{
    public function getListing(QueryBuilder $queryBuilder, ...$params);
}