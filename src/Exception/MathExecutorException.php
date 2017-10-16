<?php

/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\MathExecutor\Exception;

/**
 * @author Vitaliy Zhuk <zhuk2205@gmail.com>
 */
abstract class MathExecutorException extends \Exception
{
    const CONFIG_OTHER_ERRORS = 0;
    const CONFIG_OPERATOR_BAD_INTERFACE = 10;

    const LEXER_ERROR = 20;
    const LEXER_UNKNOWN_TOKEN = 21;
    const LEXER_UNKNOWN_FUNCTION = 22;

    const CALC_ERROR = 30;
    const CALC_UNKNOWN_VARIABLE = 31;
    const CALC_INCORRECT_EXPRESSION = 32;
    const CALC_WRONG_FUNC_ARGS = 33;
}
