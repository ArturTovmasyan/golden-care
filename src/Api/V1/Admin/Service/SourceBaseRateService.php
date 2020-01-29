<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\BaseRateNotFoundException;
use App\Entity\Assessment\Row;
use App\Entity\SourceBaseRate;
use App\Repository\BaseRateRepository;

/**
 * Class SourceBaseRateService
 * @package App\Api\V1\Admin\Service
 */
class SourceBaseRateService extends BaseService
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

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(SourceBaseRate::class), $ids);

        if (empty($entities)) {
            throw new BaseRateNotFoundException();
        }

        return $this->getRelatedData(SourceBaseRate::class, $entities);
    }
}
