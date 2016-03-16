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

/*
FIXME: remove _addObserver/_removeObserver
each \arc\object::observe call must create a new observer object
this is because it could be wrapped like this: observe(proxy(observe(target)))
the outermost observe must observe the proxy, not the target
*/
trait Observable
{
    use Proxy {
        Proxy::__construct as private ProxyConstruct;
    }

    private $observer = null;

    public function __construct( $target, $observer ) {
        $this->observer = $observer;
        $this->proxyConstruct($target);
    }

    public function __set($name, $value) {
        $changes = [
            'name' => $name,
            'object' => $this->target
        ];
        if ( property_exists($this->target, $name) ) {
            $changes['type'] = 'update';
            $changes['oldValue'] = $this->target->{$name};
        } else {
            $changes['type'] = 'add';
        }
        $this->target->{$name} = $value;
        $this->observer($changes);
    }

    public function __call($name, $params) {
        $original = \arc\object::entries($this->target);
        $result   = $this->target->{$name}(...$params);
        $changed  = array_diff_assoc( $original, \arc\object::entries($this->target));
        foreach ( $changed as $property => $changeValue ) {
            $changes = [
                'object' => $this->target,
                'name'   => $property
            ];
            if ( !isset($original[$property]) ) {
                $changes['type'] = 'add';
            } else if ( !property_exists($this->target, $property) ) {
                $changes['type'] = 'delete';
                $changes['oldValue'] = $changeValue;
            } else {
                $changes['type'] = 'update';
                $changes['oldValue'] = $original[$property];
            }
            $this->observer($changes);
        }
        return $result;
    }

    public function __unset($name) {
        if ( isset($this->target->{$name}) ) {
            $changes = [
                'object' => $this->target,
                'type' => 'delete',
                'name' => $name,
                'oldValue' => $this->target->{$name}
            ];
            unset( $this->target->{$name} );
            $this->observer($changes);
        }
    }

    public function _unobserve()
    {
        $this->observer = function() {};
        return $this->target;
    }
}