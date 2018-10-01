<?php
namespace App\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Permission
{
    /**
     * @var string
     */
    private $permissions = [];

    /**
     * Worker constructor.
     * @param $options
     */
    public function __construct($options = [])
    {
        if (!isset($options['value'])) {
            return false;
        }

        if (is_array($options['value'])) {
            $this->permissions = $options['value'];
        } else {
            $this->permissions[] = $options['value'];
        }
    }

    /**
     * @return array|string
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}