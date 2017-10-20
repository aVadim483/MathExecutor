<?php

include __DIR__ . '/src/autoload.php';

use avadim\MathExecutor\Classes\Generic\AbstractTokenOperator;
use \avadim\MathExecutor\Classes\Generic\InterfaceToken;

use \avadim\MathExecutor\Classes\Token\TokenScalarNumber;

class TokenOperatorModulus extends AbstractTokenOperator
{
    protected static $pattern = 'mod';

    /**
     * Priority of this operator (1 equals "+" or "-", 2 equals "*" or "/", 3 equals "^")
     * @return int
     */
    public function getPriority()
    {
        return 3;
    }

    /**
     * Association of this operator (self::LEFT_ASSOC or self::RIGHT_ASSOC)
     * @return string
     */
    public function getAssociation()
    {
        return self::LEFT_ASSOC;
    }

    /**
     * Execution of this operator
     * @param InterfaceToken[] $stack Stack of tokens
     *
     * @return TokenScalarNumber
     */
    public function execute(&$stack)
    {
        $op2 = array_pop($stack);
        $op1 = array_pop($stack);
        $result = $op1->getValue() % $op2->getValue();

        return new TokenScalarNumber($result);
    }
}

$calculator = new avadim\MathExecutor\MathExecutor();
$calculator->addOperator('mod', '\TokenOperatorModulus');
echo $calculator->execute('286 mod 100'), '<br>';