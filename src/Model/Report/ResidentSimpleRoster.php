<?php

namespace App\Model\Report;

class ResidentSimpleRoster extends Base
{
    /**
     * @var array
     */
    private $residents = [];

    /**
     * @return mixed
     */
    public function getResidents()
    {
        return $this->residents;
    }

    /**
     * @param $residents
     */
    public function setResidents($residents)
    {
        foreach ($residents as $resident) {
            if (!isset($this->residents[$resident['type']][$resident['typeId']]['data'])) {
                $this->residents[$resident['type']][$resident['typeId']]['data'] = [
                    'name' => $resident['typeName']
                ];
            }

            $this->residents[$resident['type']][$resident['typeId']]['floors'][$resident['floor']][] = $resident;
        }
    }
}

