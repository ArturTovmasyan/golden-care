<?php
namespace App\Util\MySQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;


class NaturalSort extends FunctionNode
{
    private $firstExpression = "";
    private $secondExpression = 10;
    private $thirdExpression = '.';

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->firstExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->secondExpression = $parser->ArithmeticExpression();
        $parser->match(Lexer::T_COMMA);
        $this->thirdExpression = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return "udf_NaturalSortFormat(" . $sqlWalker->walkSimpleArithmeticExpression($this->firstExpression) . "," .
            $sqlWalker->walkSimpleArithmeticExpression($this->secondExpression) . "," . $sqlWalker->walkStringPrimary($this->thirdExpression) . ")";
    }
}