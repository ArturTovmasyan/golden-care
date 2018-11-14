<?php
namespace App\Api\V1\Dashboard\Service;

use App\Api\V1\Common\Service\BaseService;
use App\Entity\Space;

/**
 * Class SpaceService
 * @package App\Api\V1\Dashboard\Service
 */
class SpaceService extends BaseService
{
    /**
     * @param Space $space
     * @param array $params
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function edit(Space $space, array $params): void
    {
        try {
            $this->em->getConnection()->beginTransaction();

            $space->setName($params['name'] ?? null);
            $space->setUpdatedAt(new \DateTime());
            $this->validate($space, null, ["api_dashboard_space_edit"]);

            $this->em->persist($space);
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();

            throw $e;
        }
    }
}