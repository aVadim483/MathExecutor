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
        'gt'        => '\avadim\MathExecutor\Extension\Bool\Operator\TokenOperatorGt',
        'ge'        => '\avadim\MathExecutor\Extension\Bool\Operator\TokenOperatorGe',
        'lt'        => '\avadim\MathExecutor\Extension\Bool\Operator\TokenOperatorLt',
        'le'        => '\avadim\MathExecutor\Extension\Bool\Operator\TokenOperatorLe',
        'eq'        => '\avadim\MathExecutor\Extension\Bool\Operator\TokenOperatorEq',
        'ne'        => '\avadim\MathExecutor\Extension\Bool\Operator\TokenOperatorNe',
    ],
    'functions' => [
        'compare'   => ['\avadim\MathExecutor\Extension\Bool\compare', 2, true],
        'if'        => ['\avadim\MathExecutor\Extension\Bool\if_then', 3],
    ],
];
