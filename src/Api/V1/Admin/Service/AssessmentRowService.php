<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\AssessmentRowNotFoundException;
use App\Entity\Assessment\Row;
use App\Repository\Assessment\RowRepository;

/**
 * Class AssessmentRowService
 * @package App\Api\V1\Admin\Service
 */
class AssessmentRowService extends BaseService
{
    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new AssessmentRowNotFoundException();
        }

        /** @var RowRepository $repo */
        $repo = $this->em->getRepository(Row::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(Row::class), $ids);

        if (empty($entities)) {
            throw new AssessmentRowNotFoundException();
        }

        return $this->getRelatedData(Row::class, $entities);
    }
}
