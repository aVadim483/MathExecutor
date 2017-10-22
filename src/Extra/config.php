<?php
/**
 * This file is part of the MathExecutor package
 * https://github.com/aVadim483/MathExecutor
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

return [
    'include' => [
        'functions.php',
        'Operator/*.php',
    ],
    'operators' => [
        'gt'        => '\avadim\MathExecutor\Extra\Operator\TokenOperatorGt',
        'ge'        => '\avadim\MathExecutor\Extra\Operator\TokenOperatorGe',
        'lt'        => '\avadim\MathExecutor\Extra\Operator\TokenOperatorLt',
        'le'        => '\avadim\MathExecutor\Extra\Operator\TokenOperatorLe',
        'eq'        => '\avadim\MathExecutor\Extra\Operator\TokenOperatorEq',
        'ne'        => '\avadim\MathExecutor\Extra\Operator\TokenOperatorNe',
    ],
    'functions' => [
        'compare'   => ['\avadim\MathExecutor\Extra\compare', 2, true],
        'if'        => ['\avadim\MathExecutor\Extra\if_then', 3],
    ],
];
