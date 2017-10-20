<?php
/**
 * This file is part of the MathExecutor package
 *
 * (c) Alexander Kiryukhin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace avadim\MathExecutor\Classes\Generic;

/**
 * @author Alexander Kiryukhin <alexander@symdev.org>
 */
interface InterfaceToken
{
    /**
     * @param string $sPattern
     *
     * @return array
     */
    public static function getMatching($sPattern = null);
}
