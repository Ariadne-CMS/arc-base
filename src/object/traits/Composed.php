<?php
/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace arc\object\traits;

trait Composed
{
    private $targets = [];
    private $properties = [];

    /**
     * @param object... $object A list of objects to compose into a single object
     */
    public function __construct(...$parts) {
        foreach ($parts as $key => $part) {
            if ( is_object($part) {
                $this->targets[] = $part;
            }
        }
        if ( !count($this->targets) ) {
            throw \LogicException('Nothing to compose...',1);
        }
    }

    public function __get($name) {
        for ( $i=count($this->targets)-1; $i>=0; $i--) {
            if ( isset($this->targets[$i]->{$name}) ) {
                return $this->targets[$i]->{$name};
            }
        }
        return null;
    }

    public function __set($name, $value) {
        if ( isset($this->properties[$name]) ) {
            foreach ($this->properties[$name] as $part ) {
                $part->{$name} = $value;
            }
        } else {
            $found = false;
            foreach ($this->targets as $part ) {
                if ( isset($part->{$name}) ) {
                    $part->{$name} = $value;
                    $found         = true;
                }
            }
            if ( !$found ) {
                $part = end($this->targets);
                if ( isset($part) ) {
                    $part->{$name} = $value;
                }
            }
        }
    }

    public function __unset($name) {
        if ( isset($this->properties[$name]) ) {
            foreach ($this->properties[$name] as $part) {
                unset($part)->{$name};
            }
        } else {
            $this->properties[$name] = [];
            foreach($this->targets as $part) {
                if ( isset($part->{$name}) ) {
                    unset( $part->{$name} );
                    $this->properties[$name][] = $part;
                }
            }
        }
    }

    public function __isset($name) {
        for ( $i=count($this->targets)-1; $i>=0; $i--) {
            if ( isset($this->targets[$i]->{$name}) ) {
                return true;
            }
        }
        return false;
    }

    public function __call($name, $params) {
        for ( $i=count($this->targets)-1; $i>=0; $i--) {
            if ( \arc\object::hasMethod($this->targets[$i], $name) )
                return $this->targets[$i]->{$name}(...$params);
            }
        }
        throw \arc\MethodNotFound("Method $name not found.", \arc\exceptions::OBJECT_NOT_FOUND);
    }

    public function __toString()
    {
        return implode(',',$this->targets);
    }

    public function __clone()
    {
        foreach ($this->targets as $index => $target) {
            if (is_object( $target )) {
                $this->targets[$index] = clone $target;
            }
        }
    }

    public function _hasMethod($name) {
        foreach ( $this->targets as $target) {
            if (method_exists($target, $name) ||
            ( method_exists($target, '_hasMethod') && $target->_hasMethod($name)) {
                return true;
            }
        }
        return false;
    }

    public function _entries()
    {
        $entries = [];
        foreach ( $this->targets as $target) {
            $props = array_merge($props, $this->_hasMethod('_entries') ? $this->target->_entries() : get_object_vars($this->target) );
        }
        return $props;
    }

}

?>