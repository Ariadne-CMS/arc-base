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

/**
 * @package arc
 */
class object
{
    /**
     * Creates a new Prototype object
     * @param $properties
     * @return object\Prototype
     */
    public static function prototype($properties = [])
    {
        // do not ever use a single prototype for every other object\Prototype
        // it will allow evil stuff with state shared across everything
        return new object\Prototype( $properties );
    }

    /**
     * Returns a proxy that prevents the target objects property list from
     * being changed. You can still change existing properties, but you can
     * not remove or add properties. Methods in the target object can still
     * do this.
     * @param object $ob
     * @return object\Guard
     */
    public static function seal($ob)
    {
        return new object\Guard($ob, 'seal');
    }

    /**
     * Returns a proxy that prevents the target object from changing. Property
     * changes (setting or unsetting) is disabled, calling methods creates a
     * clone and calls the method there and returns the result or the proxy
     * if the result is the target object. Doesn't intercept __get, so
     * theoretically the object can still change... but thats just wrong
     * @param object $ob
     * @return object\Guard
     */
    public static function freeze($ob)
    {
        return new object\Guard($ob, 'freeze');
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
     * @return object\Observe
     */
    public static function observe($ob, $f)
    {
        return new object\Observe($ob, $f);
    }

    /**
     * Removes an observer from an object and returns the object.
     * @param object $ob
     * @param callable $f The observer function to remove
     * @return object\Observe
     */
    public static function unobserve($ob, $f)
    {
        if ( self::hasMethod($ob, '_unobserve') ) {
            $ob = $ob->_unobserve($f);
        }
        return $ob;
    }

    /**
     * Returns a Composed object that contains all the objects
     * passed to its constructor. Any method call or property
     * access is passed on to one of its contained objects.
     * Objects passed later in the argumetns override objects
     * passed earlier. If properties exist in multiple objects,
     * they are updated in all of these. Property access (get)
     * and method calls are only done in the last matching object.
     * @param object... $ob
     * @return object\Composed
     */
    public static function compose()
    {
        return new object\Compose(func_get_args());
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
     * @return object\Protected
     */
    public static function protect($ob, $f)
    {
        return new object\Protect($ob, $f);
    }

    /**
     * Returns a proxy that adds methods with* for each property
     * and method. These create a clone, update a property or call
     * a method there and return an Immutable clone.
     * @param object $ob
     * @return object\Immutable
     */
    public static function immutable($ob)
    {
        return new object\Immutable($ob);
    }

    /**
     * Returns true if the object is frozen with \arc\object::freeze
     * @param object $ob
     * @return bool
     */
    public static function isFrozen($ob)
    {
        if ( self::hasMethod($ob, '_isGuarded') ) {
            return $ob->_isGuarded('freeze');
        }
        return false;
    }

    /**
     * Returns true if the object is frozen with \arc\object::freeze
     * or sealed with \arc\object::seal
     * @param object $ob
     * @return bool
     */
    public static function isSealed($ob)
    {
        if ( self::hasMethod($ob, '_isGuarded') ) {
            return $ob->_isGuarded('seal');
        }
        return false;
    }

    /**
     * Returns true if the object has a method with the given name.
     * @param object $ob
     * @param string $name
     * @return bool
     */
    public static function hasMethod($ob, $name)
    {
        return is_object($ob) && ( method_exists($ob, $name) ||
            (method_exists($ob, '_hasMethod') && $ob->_hasMethod($name)));
    }

    /**
     * Returns an array with all public properties of the given object.
     * @param object $ob
     * @return array
     */
    public static function entries($ob)
    {
        if ( self::hasMethod($ob, '_entries') ) {
            return $ob->_entries();
        } else if ( is_object($ob) ) {
            return get_object_vars($ob);
        } else {
            return [];
        }
    }

    /**
     * Returns a proxy that catches all null results and wraps them in \arc\object\Nothing
     *
     * @param object $target
     * @return \arc\object\Maybe
     */
    public static function maybe($target)
    {
        return new object\Something($target);
    }

    /**
     * Returns true if the given value is null or a Null object (e.g. \arc\object\Nothing)
     * @param mixed $target
     * @return bool
     */
    public static function isNull($target)
    {
        return is_null($target) || ( \arc\object::hasMethod($target, 'isNothing') && $target->isNothing() );
    }

    /**
     * Returns an object\Tainted object that wraps the raw string, or an array of objects.
     * Only wraps non-empty strings.
     * @param mixed $value
     * @return \arc\object\Tainted|mixed
     */
    public static function taint($value) {
        $result = $value;
        if ( is_array($value) ) {
            $result = [];
            foreach ($value as $key => $subvalue ) {
                $result[$key] = self::taint($subvalue);
            }
        } else if ( is_string($value) && $value ) { // empty strings don't need tainting
            $result = new object\Tainted($value);
        }
        return $result;
    }

    /**
     * Takes an object\Tainted object, or an array of them, and returns the same with
     * all object\Tainted objects replaces with their filtered values. The $filter and $flags
     * parameters are identical to the filter_var() parameters of the same name.
     * @param object\Tainted|object\Tainted[] $value
     * @param int $filter
     * @param int $flags
     * @return string|string[]|mixed
     */
    public static function untaint($value, $filter = FILTER_SANITIZE_SPECIAL_CHARS, $flags = null) {
        if ( self::isTainted($value) ) {
            $result = $value->untaint($filter, $flags);
        } else if ( is_array($value) ) {
            $result = [];
            foreach ($value as $key => $subvalue) {
                $result[$key] = self::untaint($subvalue, $filter, $flags);
            }
        }
        return $result;
    }

    /**
     * Returns true if the value is a tainted object.
     * @param mixed $value
     * @return bool
     */
    public static function isTainted($value) {
        if ( is_object($value) && ( self::hasMethod($value, 'isTainted' && $value->isTainted() ) ) ) {
            return true;
        }
        return false;
    }
}