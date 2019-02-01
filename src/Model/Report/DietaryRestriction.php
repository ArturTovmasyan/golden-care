<?php

namespace App\Model\Report;

class DietaryRestriction extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * @var array
     */
    private $data = [];

    /**
     * @param $residents
     */
    public function setResidents($residents): void
    {
        $this->residents = $residents;
    }

    /**
     * @return array
     */
    public function getResidents(): ?array
    {
        return $this->residents;
    }

    /**
     * @param $diets
     */
    public function setDiets($diets): void
    {
        foreach ($diets as $diet) {
            $this->residents[$diet['residentId']]['diets'][] = $diet;
        }
    }

    /**
     * @param $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): ?array
    {
        return $this->data;
    }
}

