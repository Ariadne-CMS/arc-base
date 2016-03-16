<?php
/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace arc\object;

/**
 * This trait implements a proxy for another object. Given this object and a
 * protection function, all accesses to this class are passed on to
 * the protected object only if the protection function returns true
 * for that access.
 * Otherwise the call or property access is ignored. Null is returned
 * where appropriate.
 * The protector function must accept upto three arguments:
 * - string $access One of 'get','set','isset','unset','call' and 'proxy'
 * - string $name The name of the property or method
 * - mixed  $value The value passed
 * The 'proxy' access type is used when a method call returns an object.
 * In that case the protection method is called with access type 'proxy',
 * the name of the method called and the result of that method.
 * If the protection method returns true, the result object is also
 * protected, with the same protection function.
 */


final class Tainted implements \ArrayAccess, \Countable, \jsonSerializable {

    private $value = null;

    public function __construct($value)
    {
        if (!is_string($value)) {
            throw new \logicException('Not a string');
        }
        $this->value = $value;
    }

    public function isTainted()
    {
        return true;
    }

    public function untaint($filter = FILTER_SANITIZE_SPECIAL_CHARS, $flags = null)
    {
        return filter_var($this->value, $filter, $flags);
    }

    public function __toString()
    {
        return $this->untaint();
    }

    public function jsonSerialize()
    {
        return $this->untaint();
    }

    public function offsetGet($offset)
    {
        return $this->value[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->value[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        // this forces the same error as if the string wasn't tainted
        unset($this->value[$offset]);
    }

    public function offsetExists($offset)
    {
        return isset($this->value[$offset]);
    }

    public function count()
    {
        return 1;
    }

}