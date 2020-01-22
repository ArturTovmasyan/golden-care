<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\BaseRateNotFoundException;
use App\Entity\Assessment\Row;
use App\Entity\BaseRate;
use App\Repository\BaseRateRepository;

/**
 * Class BaseRateService
 * @package App\Api\V1\Admin\Service
 */
class BaseRateService extends BaseService
{
    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new BaseRateNotFoundException();
        }

        /** @var BaseRateRepository $repo */
        $repo = $this->em->getRepository(Row::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(BaseRate::class), $ids);

        if (empty($entities)) {
            throw new BaseRateNotFoundException();
        }

        return $this->getRelatedData(BaseRate::class, $entities);
    }
}
