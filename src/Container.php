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


class Container
{
    private $data;

    public function has($name)
    {
        return isset($this->data[$name]);
    }

    public function get($name)
    {
        return $this->data[$name];
    }

    public function set($name, $object)
    {
        return $this->data[$name] = $object;
    }

    public function __call($method, $arguments)
    {
        if (0 === strpos($method, 'get')) {
            $name = substr($method, 3);
            if ($this->has($name)) {
                return $this->get($name);
            }
        } elseif (0 === strpos($method, 'set')) {
            $name = substr($method, 3);
            return $this->get($name, reset($arguments));
        }
    }

}

// EOF