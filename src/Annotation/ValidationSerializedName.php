<?php

namespace App\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ValidationSerializedName
{
    /**
     * @var string
     */
    private $names = [];

    /**
     * Worker constructor.
     * @param $options
     */
    public function __construct($options = [])
    {
        if (!isset($options)) {
            return;
        }

        if (is_array($options)) {
            $this->names = $options;
        } else {
            $this->names[] = $options;
        }
    }

    /**
     * @return array|string
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * @return array|string
     */
    public function getName($groupName)
    {
        return array_key_exists($groupName, $this->names) ? $this->names[$groupName] : null;
    }
}