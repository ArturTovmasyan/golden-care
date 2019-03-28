<?php

namespace App\Util\MySQL;

use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\MysqlJsonFunctionNode;

/**
 * "JSON_ARRAYAGG" "(" { NewValue }* ")"
 */
class JsonArrayAgg extends MysqlJsonFunctionNode
{
	const FUNCTION_NAME = 'JSON_ARRAYAGG';

    /** @var string[] */
    protected $optionalArgumentTypes = [self::VALUE_ARG];

    /** @var bool */
    protected $allowOptionalArgumentRepeat = true;
}
