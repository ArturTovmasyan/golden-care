<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\FacilityBedNotFoundException;
use App\Entity\FacilityBed;
use App\Repository\FacilityBedRepository;

/**
 * Class FacilityBedService
 * @package App\Api\V1\Admin\Service
 */
class FacilityBedService extends BaseService
{
    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new FacilityBedNotFoundException();
        }

        /** @var FacilityBedRepository $repo */
        $repo = $this->em->getRepository(FacilityBed::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(FacilityBed::class), $ids);

        if (empty($entities)) {
            throw new FacilityBedNotFoundException();
        }

        return $this->getRelatedData(FacilityBed::class, $entities);
    }
}
