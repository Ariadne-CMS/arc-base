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

final class Something implements \ArrayAccess, \IteratorAggregate, \Countable, \jsonSerializable, Maybe {

    use \arc\traits\Proxy;
    use \arc\traits\ArrayProxy;

    public function __get($name)
    {
    	return !isset($this->target->{$name}) ? new Nothing() : new Something($this->target->{$name} );    	
    }

    public function __call($name, $params)
    {
    	$result = call_user_func_array([$this->target,$name], $params);
    	return !isset($result) ? new Nothing() : new Something($result);
    }

    public function offsetGet($offset)
    {
    	return !isset($this->target[$offset]) ? new Nothing() : new Something($this->target[$offset]);
    }

    public function isNothing()
    {
        return is_null($this->target);
    }

    public function getValueOr($ifNothing)
    {
    	return !is_null($this->target) ? $this->target : (
            is_callable($ifNothing) ? $ifNothing() : $ifNothing 
        );
    }

    public function count()
    {
    	return count($this->target);
    }

    public function getIterator()
    {
    	return new ArrayIterator($this);
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
        throw new \LogicException('You must not use \\arc\\object\\Something directly, call getValueOr() instead');
    }

    public function __sleep()
    {
        throw new \LogicException('You must not use \\arc\\object\\Something directly, call getValueOr() instead');
    }

    public function jsonSerialize()
    {
        throw new \LogicException('You must not use \\arc\\object\\Something directly, call getValueOr() instead');
    }
}