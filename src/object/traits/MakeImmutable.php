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

trait MakeImmutable
{
    use Proxy {
        Proxy::__construct as private ProxyConstruct;
    }

    function __set($name, $value)
    {
    }

    function __call($name, $args)
    {
        $clone  = clone $this->target;
        $result = null;
        if (substr($name, 0, 4) == 'with') {
            $name = lcfirst(substr($name,4));
            // check method with name
            if ( \arc\object::hasMethod($name) ) {
                $result = call_user_func_array([$clone,$name], $args);
            } else { // set property
                $clone->{$name} = $value;
            }
            if ( $result === $clone ) {
                return $this;
            } else {
                return new self($clone);
            }
        } else {
            return call_user_func_array([$clone,$name], $args);
        }
    }
}