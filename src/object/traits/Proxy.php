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

trait Proxy
{
    protected $target;

    public function __construct($target)
    {
        $this->target = $target;
    }

    public function __get($name)
    {
        return $this->target->{$name};
    }

    public function __set($name, $value)
    {
        $this->target->{$name} = $value;
    }

    public function __unset($name)
    {
        unset($this->target->{$name});
    }

    public function __isset($name)
    {
        return isset( $this->target->{$name} );
    }

    public function __call($name, $args)
    {
        return call_user_func_array( [ $this->target, $name ], $args );
    }

    public function __clone()
    {
        if (is_object( $this->target )) {
            $this->target = clone $this->target;
        }
    }

    public function __toString()
    {
        return (string) $this->target;
    }

    public function _hasMethod($name) {
        return method_exists($this->target, $name) ||
            ( method_exists($this->target, '_hasMethod') && $this->target->_hasMethod($name));
    }

    public function _entries()
    {
        return $this->_hasMethod('_entries') ? $this->target->_entries() : get_object_vars($this->target);
    }

}

?>