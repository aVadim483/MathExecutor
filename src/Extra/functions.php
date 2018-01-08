<?php
/**
 * This file is part of the MathExecutor package
 * https://github.com/aVadim483/MathExecutor
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\MathExecutor\Extra;

/**
 * @param $val1
 * @param $val2
 * @param null $cond
 *
 * @return int
 */
function compare($val1, $val2, $cond = null)
{
    if (null === $cond) {
        if (is_numeric($val1) && is_numeric($val2)) {
            if ($val1 < $val2) {
                return -1;
            }
            if ($val1 > $val2) {
                return 1;
            }
            return 0;
        }
    }
    switch ($cond) {
        case '<':
        case 'lt':
            return ($val1 < $val2) ? 1 : 0;
        case '<=':
        case 'le':
        case 'lte':
            return ($val1 <= $val2) ? 1 : 0;
        case '>':
        case 'gt':
            return ($val1 > $val2) ? 1 : 0;
        case '>=':
        case 'ge':
        case 'gte':
            return ($val1 >= $val2) ? 1 : 0;
        case '==':
        case '=':
        case 'eq':
            return ($val1 == $val2) ? 1 : 0;
        case '!=':
        case '<>':
        case 'ne':
            return ($val1 != $val2) ? 1 : 0;
        default:
            throw new \RuntimeException('Unknown compare operator "' . $cond . '"');
    }

}

/**
 * @param mixed $cond
 * @param mixed $val1 Returns if $cond is true
 * @param mixed $val2 Returns if $cond is false
 *
 * @return mixed
 */
function if_then($cond, $val1, $val2)
{
    if ((bool)$cond) {
        return $val1;
    }
    return $val2;
}
