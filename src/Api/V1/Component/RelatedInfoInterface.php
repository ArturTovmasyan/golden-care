<?php

namespace App\Api\V1\Component;

use App\Entity\Space;

/**
 * Interface RelatedInfoInterface
 * @package App\Api\V1\Component
 */
interface RelatedInfoInterface
{
    /**
     * @param Space|null $space
     * @param array|null $entityGrants
     * @param null $mappedBy
     * @param null $id
     * @param array|null $ids
     * @return mixed
     */
    public function getRelatedData(Space $space = null, array $entityGrants = null, $mappedBy = null, $id = null, array $ids = null);
}
