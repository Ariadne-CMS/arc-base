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

trait Protected
{
    use Proxy {
        Proxy::__construct as private ProxyConstruct;
    }

    private $protector;

    public function __construct( $target, $protector ) {
        $this->protector = $protector;
        $this->proxyConstruct($target);
    }

    public function __set($name, $value) {
        if ( $this->protector('set', $name, $value) ) {
            $this->target->{$name} = $value;
        }
    }

    public function __call($name, $params) {
        if ( $this->protector('call', $name, $params) ) {
            $result = call_user_func_array( [$this->target, $name], $params);
            if ( $result === $this->target ) {
                return $this;
            } else if ( is_object($result) && $this->protector('proxy', $name, $result) ) {
                return new static($result, $this->protector);
            } else {
                return $result;
            }
        }
    }

    public function __unset($name) {
        if ( $this->protector('unset', $name ) ) {
            unset $this->target->{$name};
        }
    }

    public function __get($name) {
        if ( $this->protector('get', $name) ) {
            return $this->target->{$name};
        }
    }

    public function __isset($name) {
        if ( $this->protector('isset', $name) ) {
            return isset($this->target->{$name});
        }
    }
}