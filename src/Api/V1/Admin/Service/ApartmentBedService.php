<?php

namespace App\Api\V1\Admin\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Api\V1\Common\Service\Exception\ApartmentBedNotFoundException;
use App\Entity\ApartmentBed;
use App\Repository\ApartmentBedRepository;

/**
 * Class ApartmentBedService
 * @package App\Api\V1\Admin\Service
 */
class ApartmentBedService extends BaseService
{
    /**
     * @param array $ids
     * @return array
     */
    public function getRelatedInfo(array $ids): array
    {
        if (empty($ids)) {
            throw new ApartmentBedNotFoundException();
        }

        /** @var ApartmentBedRepository $repo */
        $repo = $this->em->getRepository(ApartmentBed::class);

        $entities = $repo->findByIds($this->grantService->getCurrentSpace(), $this->grantService->getCurrentUserEntityGrants(ApartmentBed::class), $ids);

        if (empty($entities)) {
            throw new ApartmentBedNotFoundException();
        }

        return $this->getRelatedData(ApartmentBed::class, $entities);
    }
}
