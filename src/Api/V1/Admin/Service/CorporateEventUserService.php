<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\CorporateEventUserNotFoundException;
use App\Entity\Assessment\Row;
use App\Entity\CorporateEventUser;
use App\Repository\CorporateEventUserRepository;

/**
 * Class CorporateEventUserService
 * @package App\Api\V1\Admin\Service
 */
class CorporateEventUserService extends BaseService
{
    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new CorporateEventUserNotFoundException();
        }

        /** @var CorporateEventUserRepository $repo */
        $repo = $this->em->getRepository(Row::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(CorporateEventUser::class), $ids);

        if (empty($entities)) {
            throw new CorporateEventUserNotFoundException();
        }

        return $this->getRelatedData(CorporateEventUser::class, $entities);
    }
}
