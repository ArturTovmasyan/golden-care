<?php

namespace  App\Model;

class ChangeLogType
{
    const TYPE_NEW_LEAD     = 1;
    const TYPE_LEAD_UPDATED = 2;
    const TYPE_NEW_TASK     = 3;
    const TYPE_TASK_UPDATED = 4;

    /**
     * @var array
     */
    private static $types = [
        self::TYPE_NEW_LEAD => 'New Lead',
        self::TYPE_LEAD_UPDATED => 'Lead Updated',
        self::TYPE_NEW_TASK => 'New Task',
        self::TYPE_TASK_UPDATED => 'Task Updated',
    ];

    /**
     * @var array
     */
    private static $typeDefaultNames = [
        'New Lead' => '1',
        'Lead Updated' => '2',
        'New Task' => '3',
        'Task Updated' => '4',
    ];

    /**
     * @var array
     */
    private static $typeValues = [
        self::TYPE_NEW_LEAD => 1,
        self::TYPE_LEAD_UPDATED => 2,
        self::TYPE_NEW_TASK => 3,
        self::TYPE_TASK_UPDATED => 4,
    ];

    /**
     * @return array
     */
    public static function getTypes()
    {
        return self::$types;
    }

    /**
     * @return array
     */
    public static function getTypeDefaultNames()
    {
        return self::$typeDefaultNames;
    }

    /**
     * @return array
     */
    public static function getTypeValues()
    {
        return self::$typeValues;
    }
}

