<?php
/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace arc;

function singleton($f) {
    return function () use ($f) {
        static $result;
        if (null === $result) {
            if ( $f instanceof \Closure && isset($this) ) {
                $f = \Closure::bind($f, $this);
            }
            $result = $f();
        }
        return $result;
    };
}

function partial(callable $callable, $partialArgs, $defaultArgs=[] ) {
    $partialMerge = function($partialArgs, $addedArgs, $defaultArgs = [])
    {
        end( $partialArgs );
        $l = key( $partialArgs );
        for ($i = 0; $i <= $l; $i++) {
            if (!array_key_exists($i, $partialArgs) && count($addedArgs)) {
                $partialArgs[ $i ] = array_shift( $addedArgs );
            }
        }
        if (count($addedArgs)) { // there are $addedArgs left, so there should be no 'holes' in $partialArgs
            $partialArgs =array_merge( $partialArgs, $addedArgs );
        }
        // fill any 'holes' in $partialArgs with entries from $defaultArgs
        $result =  array_replace( $defaultArgs, $partialArgs );
        ksort($result);

        return $result;
    };

    return function() use ($callable, $partialArgs, $defaultArgs, $partialMerge) {
        if ( $callable instanceof \Closure && isset($this) ) {
            $callable = \Closure::bind($callable, $this);
        }
        return call_user_func_array( $callable, $partialMerge( $partialArgs, func_get_args(), $defaultArgs ) );
    };
}

/**
 * Class lambda
 * Experimental functionality, may be removed later, use at own risk.
 * @package arc
 */
class lambda
{
    /**
     * Creates a new Prototype object
     * @param $properties
     * @return lambda\Prototype
     */
    public static function prototype($properties = [])
    {
        // do not ever use a single prototype for every other lambda\Prototype
        // it will allow evil stuff with state shared across everything
        return new lambda\Prototype( $properties );
    }

    /**
     * Returns a function with the given arguments already entered or partially applied.
     * @param callable $callable The function to curry
     * @param array $partialArgs unlimited Optional arguments to curry the function with
     * @param array $defaultArgs optional default values
     * @return callable
     */
    public static function partial(callable $callable, $partialArgs, $defaultArgs = [])
    {
        return partial($callable, $partialArgs, $defaultArgs);
    }


    /**
     * Returns a function with named arguments. The peppered function accepts one argument - a named array of values
     * @param callable $callable The function or method to pepper
     * @param array $namedArgs Optional. The named arguments to pepper the function with, the order must be the order
     *        in which the unpeppered function expects them. If not set, pepper will use Reflection to get them.
     *        Format is [ 'argumentName' => 'defaultValue' ]
     * @return callable
     */
    public static function pepper(callable $callable, $namedArgs=null)
    {
        if ( !is_array( $namedArgs ) ) {
            $ref = !is_array($callable) ? new \ReflectionFunction($callable) : new \ReflectionMethod($callable[0], $callable[1]);
            $namedArgs = [];
            foreach ($ref->getParameters() as $parameter) {
                $namedArgs[ $parameter->getName() ] = $parameter->getDefaultValue();
            }
        }

        return function ($otherArgs) use ($callable, $namedArgs) {
            $args = array_values( array_merge( $namedArgs, $otherArgs ) );
            return call_user_func_array( $callable, $args );
        };
    }

    /**
    * Returns a method that will generate and call the given function only once and return its result for every call.
    * The first call generates the result. Each subsequent call simply returns that same result. This allows you
    * to create in-context singletons for any kind of object.
    * <code>
    *   $proto = \arc\lambda::prototype([
    *     'getSingleton' => \arc\lambda::singleton( function () {
    *       return new ComplexObject();
    *     })
    *   ]);
    * </code>
    * @param callable $f The function to generate the singleton.
    * @return mixed The singleton.
    */
    public static function singleton($f)
    {
        return singleton($f);
    }

    /**
     * Returns a proxy that prevents the target objects property list from
     * being changed. You can still change existing properties, but you can
     * not remove or add properties. Methods in the target object can still
     * do this.
     * @param object $ob
     * @return lambda\Guard
     */
    public static function seal($ob) {
        return new lambda\Guard($ob, 'seal');
    }

    /**
     * Returns a proxy that prevents the target object from changing. Property
     * changes (setting or unsetting) is disabled, calling methods creates a 
     * clone and calls the method there and returns the result or the proxy
     * if the result is the target object. Doesn't intercept __get, so 
     * theoretically the object can still change... but thats just wrong
     * @param object $ob
     * @return lambda\Guard
     */
    public static function freeze($ob) {
        return new lambda\Guard($ob, 'freeze');
    }

    /**
     * Returns a proxy that calls an observer for any change in the properties
     * of the target object. The observer function must accept an array of changes
     * with the following contents
     * - 'type' => 'add','update' or 'delete'
     * - 'object' => the target object
     * - 'name' => the name of the property changed
     * - 'oldValue' => the previous value, if available
     * @param object $ob
     * @param callable $f
     * @return lambda\Observe
     */
    public static function observe($ob, $f) {
        if ( method_exists($ob, '_addObserver') ) {
            $ob->_addObserver($f);
            return $ob;
        } else {
            return new lambda\Observe($ob, $f);
        }
    }

    /**
     * Removes an observer from an object and returns the object.
     * @param object $ob
     * @param callable $f The observer function to remove
     * @return lambda\Observe
     */
    public static function unobserve($ob, $f) {
        if ( method_exists($ob, '_removeObserver') ) {
            $ob->_removeObserver($f);
        }
        return $ob;
    }

    /**
     * Returns a proxy that allows a protector function to check whether a
     * specific access to the target should be allowed.
     * For each get, set, unset, isset and call to a method, the protector 
     * method is called with up to three arguments:
     * - $type 'get','set','unset','isset' or 'call'
     * - $name the name of the property or method
     * - $value the new value or the parameters to the method as an array
     * @param object $ob
     * @param callable $f
     * @return lambda\Protect
     */
    public static function protect($ob, $f) {
        return new lambda\Protect($ob, $f);
    }

    public static function isFrozen($ob) {
        if ( method_exists($ob, '_isGuarded') ) {
            return $ob->_isGuarded('freeze');
        }
        return false;
    }

    public static function isSealed($ob) {
        if ( method_exists($ob, '_isGuarded') ) {
            return $ob->_isGuarded('seal');
        }
        return false;
    }

}
