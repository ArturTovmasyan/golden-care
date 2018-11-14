<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\IGridService;
use App\Entity\Medication;
use Doctrine\ORM\QueryBuilder;

/**
 * Class MedicationService
 * @package App\Api\V1\Dashboard\Service
 */
class MedicationService extends BaseService implements IGridService
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param $params
     * @return mixed
     */
    public function getListing(QueryBuilder $queryBuilder, $params)
    {
        return $this->em->getRepository(Medication::class)->search($queryBuilder);
    }
}