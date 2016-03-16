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

trait Guarded
{
    use Proxy {
        Proxy::__construct as private ProxyConstruct;
    }

    private $protectionLevel;

    public function __construct( $target, $protectionLevel='freeze' ) {
        $this->protectionLevel = $protectionLevel;
        $this->proxyConstruct($target);
    }

    public function __set($name, $value) {
        if ( $this->protectionLevel == 'seal' && isset( $this->target->{$name} ) ) {
            $this->target->{$name} = $value;
        }
    }

    public function __call($name, $params) {
        if ( $this->protectionLevel == 'seal' ) {
            return $this->target->{$name}(...$params);
        } else {
            $clone = clone $this->target;
            $result = $clone->{$name}(...$params);
            if ( $result == $clone ) {
                return $this;
            } else {
                return $result;
            }
        }
    }

    public function __unset($name) {
    }

    public function _isGuarded($checkLevel) {
        switch ( $this->protectionLevel ) {
            case 'freeze':
                if ( $checkLevel == 'freeze' ) {
                    return true;
                }
                //FALLTHROUGH
            case 'seal':
                if ( $checkLevel == 'seal' ) {
                    return true;
                }
                break;
        }
        return false;
    }

?>