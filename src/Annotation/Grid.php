<?php
namespace App\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Grid
{
    /**
     * @var array
     */
    private $groups = [];

    public const FIELD_OPTIONS = [
        0 => 'label',
        1 => 'type',
        2 => 'sortable',
        3 => 'filterable',
    ];

    /**
     * @param $options
     */
    public function __construct($options = null)
    {
        if (empty($options)) {
            return false;
        }

        foreach ($options as $groupName => $groupOptions) {
            foreach ($groupOptions as $index => $groupOption) {
                foreach ($groupOption as $key => $fieldOption) {
                    if (isset(self::FIELD_OPTIONS[$key])) {
                        $this->groups[$groupName][$index][self::FIELD_OPTIONS[$key]] = $fieldOption;
                    }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param $groupName
     * @return array|boolean
     */
    public function getGroup($groupName)
    {
        if (isset($this->groups[$groupName])) {
            return $this->groups[$groupName];
        }

        return false;
    }
}