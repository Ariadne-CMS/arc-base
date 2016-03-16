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

final class Nothing extends \Exception implements \ArrayAccess, \IteratorAggregate, \Countable, \jsonSerializable, Maybe {

    private static $errorHandler = null;

    public function __get($name)
    {
    	return $this;
    }

    public function __call($name, $params)
    {
    	return $this;
    }

    public function offsetGet($offset)
    {
    	return $this;
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }

    public function offsetExists($offset)
    {
    	return false;
    }

    public function getValueOr($ifNothing)
    {
    	return is_callable($ifNothing) ? $ifNothing() : $ifNothing;
    }

    public function isNothing()
    {
    	return true;
    }

    public function __invoke()
    {
    	return $this; 
    }

    public function __toString()
    {
        self::$errorHandler = set_error_handler(['\arc\object\Nothing','errorHandler']);        
		return null; // will trigger a non fatal A_RECOVERABLE_ERROR, which can be caught 
    }

    public static function errorHandler($errorNumber, $errorMessage, $errorFile, $errorLine)
    {
        set_error_handler(self::$errorHandler);
        self::$errorHandler = null;
        throw $this;
    }

    public function __set($name, $value)
    {
    }

    public function __isset($name)
    {
        return isset( $this->target->{$name} );
    }

    public function count()
    {
    	return 0;
    }

    public function getIterator()
    {
    	return new ArrayIterator($this);
    }

    public function __sleep()
    {
        throw $this;
    }

    public function jsonSerialize()
    {
        throw $this;
    }

}