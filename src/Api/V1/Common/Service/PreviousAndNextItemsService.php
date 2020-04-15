<?php

namespace App\Api\V1\Common\Service;

interface PreviousAndNextItemsService
{
    /**
     * @return int|null
     */
    public function getPreviousId(): ?int;

    /**
     * @param int|null $previousId
     */
    public function setPreviousId(?int $previousId): void;

    /**
     * @return int|null
     */
    public function getNextId(): ?int;

    /**
     * @param int|null $nextId
     */
    public function setNextId(?int $nextId): void;
}