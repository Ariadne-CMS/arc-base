<?php

namespace arc;

/**
 * Class hash
 * @package arc
 */
class hash
{
    /**
     * Returns the value from $hash matching the given path ($path) or
     * if the path cannot be found in the hash, it returns the default
     * value ($default).
     * @param      $path    A list of keys to traverse, seperated by '/'
     * @param      $hash    The hash to search
     * @param null $default The default value if the path is not found.
     * @return mixed|null
     */
    public static function get($path, $hash, $default = null)
    {
        $result = \arc\path::reduce( $path, function ($result, $item) {
            $item = rawurldecode($item); //FIXME: this may be unexpected
            if (is_array( $result ) && array_key_exists( $item, $result )) {
                return $result[$item];
            }
        }, $hash );
        return isset($result) ? $result : $default;
    }

    /**
     * Checks whether the given path ($path) is available in the hash.
     * @param $path A list of keys to traverse, seperated by '/'
     * @param $hash The hash to search
     * @return bool
     */
    public static function exists($path, $hash)
    {
        $path = \arc\path::collapse($path);
        $parent = \arc\path::parent($path);
        $filename = basename( $path );
        $hash = self::get( $parent, $hash );

        return (is_array($hash) && array_key_exists( $filename, $hash ));
    }

    /**
     * Parses a name like name[index][index2] to /name/index/index2/
     * @param $name The variable name to parse
     * @return string
     */
    public static function parseName($name)
    {
        $elements = explode( '[', $name );
        $path = array();
        foreach ($elements as $element) {
            if ($element[ strlen($element) -1 ] === ']') {
                $element = substr($element, 0, -1);
            }
            if ($element[0] === "'") {
                $element = substr($element, 1, -1);
            }
            $path[] = rawurlencode($element);
        }

        return '/'.implode( '/', $path ).'/';
    }

    /**
     * Compiles a path like /name/index/index2/ to name[index][index2]
     * @param        $path
     * @param string $root
     * @return mixed
     */
    public static function compileName($path, $root = '')
    {
        return \arc\path::reduce( $path, function ($result, $item) {
            $item = rawurldecode($item);

            return (!$result ? $item : $result . '[' . $item . ']');
        }, $root );
    }

    /**
     * Converts a hash to a \arc\tree\NamedNode
     * @param      $hash
     * @param null $parent
     * @return tree\NamedNode|null
     */
    public static function tree($hash, $parent = null)
    {
        if (!isset( $parent )) {
            $parent = \arc\tree::expand();
        }
        if (is_array( $hash ) || $hash instanceof \Traversable) {
            foreach ($hash as $index => $value) {
                $child = $parent->appendChild( $index );
                if (is_array( $value )) {
                    self::tree( $value, $child );
                } else {
                    $child->nodeValue = $value;
                }
            }
        } else {
            $parent->nodeValue = $hash;
        }

        return $parent;
    }
}
