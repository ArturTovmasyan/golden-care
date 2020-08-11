<?php

namespace App\Model\Report\Lead;

use App\Model\Report\Base;

class ContactList extends Base
{
    /**
     * @var array
     */
    private $contacts = [];

    /**
     * @param $contacts
     */
    public function setContacts($contacts): void
    {
        $this->contacts = $contacts;
    }

    /**
     * @return array
     */
    public function getContacts(): ?array
    {
        return $this->contacts;
    }
}

