<?php

namespace App\Model\Report;

use App\Model\ContractType;
use App\Model\Phone;

class SixtyDays extends Base
{
    /**
     * @var array
     */
    private $contracts = [];

    /**
     * @var string
     */
    private $date;

    /**
     * @return array
     */
    public function getContracts()
    {
        return $this->contracts;
    }

    /**
     * @param $contracts
     */
    public function setContracts($contracts)
    {
        foreach ($contracts as $key => $contract) {
            $contract['responsiblePersonPhoneType'] = Phone::$typeNames[$contract['responsiblePersonPhoneType']];

            $this->contracts[$contract['type']]['data'][] = $contract;
            $this->contracts[$contract['type']]['name']   = ContractType::getTypes()[$contract['type']];
            $this->contracts[$contract['type']]['type']   = $contract['type'];
        }
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate(string $date): void
    {
        $this->date = $date;
    }
}

