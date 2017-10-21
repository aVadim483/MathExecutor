<?php
/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\MathExecutor;

use avadim\MathExecutor\Generic\AbstractTokenOperator;
use avadim\MathExecutor\Generic\AbstractTokenScalar;

use avadim\MathExecutor\Token\TokenFunction;
use avadim\MathExecutor\Token\TokenIdentifier;
use avadim\MathExecutor\Token\TokenLeftBracket;
use avadim\MathExecutor\Token\TokenScalarNumber;
use avadim\MathExecutor\Token\TokenVariable;

use avadim\MathExecutor\Exception\CalcException;

/**
 * Class Calculator
 *
 * @package avadim\MathExecutor\Classes
 */
class Calculator
{
    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    private $logEnable = false;
    private $log = [];

    /**
     * Calculator constructor.
     *
     * @param $tokenFactory
     */
    public function __construct($tokenFactory)
    {
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function logEnable($flag)
    {
        $this->logEnable = (bool)$flag;

        return $this;
    }

    /**
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * @param TokenFunction $token
     * @param array         $stack
     */
    protected function executeToken($token, &$stack)
    {
        $token->setCalculator($this);
        $oldStack = $stack;
        $stack[] = $token->execute($stack);
        if ($this->logEnable) {
            $args = [];
            $count = count($oldStack);
            for ($i = 0; $i < $count; $i++) {
                if (isset($stack[$i]) && $stack[$i] === $oldStack[$i]) {
                    continue;
                }
                $args[] = $oldStack[$i]->getValue();
            }
            $tokenStr = (string)$token->getValue();
            $this->log[] = [$tokenStr, $args, end($stack)->getValue()];
        }
    }

    /**
     * Calculate array of tokens in reverse polish notation
     *
     * @param  array $tokens    Array of tokens
     * @param  array $variables Array of variables
     *
     * @return int|float
     *
     * @throws CalcException
     */
    public function calculate($tokens, $variables)
    {
        $stack = [];
        foreach ($tokens as $token) {
            if ($token instanceof TokenFunction) {
                $this->executeToken($token, $stack);
            } elseif ($token instanceof AbstractTokenOperator) {
                if (empty($stack)) {
                    throw new CalcException('Incorrect expression ', CalcException::CALC_INCORRECT_EXPRESSION);
                }
                $this->executeToken($token, $stack);
            } elseif ($token instanceof TokenLeftBracket) {
                $stack[] = $token;
            } elseif ($token instanceof AbstractTokenScalar || $token instanceof TokenIdentifier) {
                $stack[] = $token;
            } elseif ($token instanceof TokenVariable) {
                $variable = $token->getValue();
                if (!array_key_exists($variable, $variables)) {
                    throw new CalcException('Unknown variable "' . $variable . '"', CalcException::CALC_UNKNOWN_VARIABLE);
                }
                $value = $variables[$variable];
                $stack[] = new TokenScalarNumber($value);
            }
        }
        $result = array_pop($stack);
        if (!empty($stack)) {
            throw new CalcException('Incorrect expression ', CalcException::CALC_INCORRECT_EXPRESSION);
        }

        return $result->getValue();
    }
}
