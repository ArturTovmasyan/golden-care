<?php

namespace App\Model\Report;

class PhysicianFull extends Base
{
    /**
     * @var array
     */
    private $physicians = [];

    /**
     * @var string
     */
    private $type;

    /**
     * @return mixed
     */
    public function getPhysicians()
    {
        return $this->physicians;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $physicians
     */
    public function setPhysicians($physicians)
    {
        $physiciansByType = [];

        foreach ($physicians as $physician) {
            if (!isset($physiciansByType[$physician['type']][$physician['typeId']]['name'])) {
                $physiciansByType[$physician['type']][$physician['typeId']]['name'] = $physician['name'];
            }

            $physiciansByType[$physician['type']][$physician['typeId']]['data'][] = $physician;
        }

        $this->physicians = $physiciansByType;
    }
}

