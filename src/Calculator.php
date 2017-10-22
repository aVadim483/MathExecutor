<?php
/**
 * This file is part of the MathExecutor package
 * https://github.com/aVadim483/MathExecutor
 *
 * Based on NeonXP/MathExecutor by Alexander Kiryukhin
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
 * @package avadim\MathExecutor
 */
class Calculator
{
    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    private $functions = [];
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
     * @param bool          $return
     *
     * @return TokenScalarNumber
     */
    protected function executeToken($token, &$stack, $return = false)
    {
        $token->setCalculator($this);
        $oldStack = $stack;
        $result = $token->execute($stack);
        if (!$return) {
            $stack[] = $result;
        }
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
            $this->log[] = [$tokenStr, $args, $result->getValue()];
        }
        return $result;
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

    /**
     * @param $name
     * @param $stack
     *
     * @return TokenScalarNumber
     */
    public function callFunction($name, &$stack)
    {
        if (!isset($this->functions[$name])) {
            $this->functions[$name] = $this->tokenFactory->createFunction($name);
        }
        return $this->executeToken($this->functions[$name], $stack, true);
    }

}
