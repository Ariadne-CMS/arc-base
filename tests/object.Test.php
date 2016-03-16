<?php

    /*
     * This file is part of the Ariadne Component Library.
     *
     * (c) Muze <info@muze.nl>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */

    class TestObject extends PHPUnit_Framework_TestCase
    {
        function testPrototype()
        {
            $view = \arc\object::prototype( [
                'foo' => 'bar',
                'bar' => function () {
                    return $this->foo;
                }
            ] );
            $this->assertEquals( $view->foo, 'bar' );
            $this->assertEquals( $view->bar(), 'bar' );
        }

        function testPrototypeInheritance()
        {
            $foo = \arc\object::prototype( [
                'foo' => 'bar',
                'bar' => function () {
                    return $this->foo;
                }
            ]);
            $bar = $foo->extend( [
                'foo' => 'rab'
            ]);
            $this->assertEquals( $foo->foo, 'bar' );
            $this->assertEquals( $bar->foo, 'rab' );
            $this->assertEquals( $foo->bar(), 'bar' );
            $this->assertEquals( $bar->bar(), 'rab' );
            $this->assertTrue( $bar->hasOwnProperty('foo') );
            $this->assertFalse( $bar->hasOwnProperty('bar') );

        }

        function testPrototypeInheritance2()
        {
            $foo = \arc\object::prototype([
                'bar' => function () {
                    return 'bar';
                }
            ]);
            $bar = $foo->extend([
                'bar' => function () use ($foo) {
                    return 'foo'.$foo->bar();
                }
            ]);
            $this->assertEquals( $bar->bar(), 'foobar' );
        }

        function testPrototypeInheritance3()
        {
            $foo = \arc\object::prototype([
                'bar' => function () {
                    return 'bar';
                },
                'foo' => function () {
                    return '<b>'.$this->bar().'</b>';
                }
            ]);
            $bar = $foo->extend([
                'bar' => function () use ($foo) {
                    return 'foo'.$foo->bar();
                }
            ]);
            $this->assertEquals( $bar->foo(), '<b>foobar</b>' );
        }


        function testToString()
        {
            $foo = \arc\object::prototype([
                'foofoo' => function () {
                    return 'foofoo';
                },
                '__toString' => function () {
                    return 'foobar';
                },
            ]);
            $tst = (string)$foo;
            $this->assertEquals( 'foobar', $tst);
        }

        function testExtend()
        {
            $empty = \arc\object::prototype();
            $foo = $empty->extend([
                'foo' => function() {
                    return 'foo';
                }
            ]);
            $this->assertEquals($empty, $foo->prototype);
            $this->assertEquals('foo', $foo->foo());
        }

        function testMaybe()
        {
            $foo = \arc\object::maybe(new CanHaveNull())->doNothing()->getFoo()->getValueOr('This is null');
            $null = \arc\object::maybe(new CanHaveNull())->doNothing()->null->getValueOr('This is null');
            $this->assertEquals('bar', $foo);
            $this->assertEquals('This is null', $null);
            $null = \arc\object::maybe(new CanHaveNull())->null->doesNotExist()->getValueOr('This is null');
            $this->assertEquals('This is null', $null);
            $this->assertTrue(\arc\object::isNull(\arc\object::maybe(new CanHaveNull())->null->doesNotExist()));
            $this->assertTrue(\arc\object::isNull(null));
            $this->assertFalse(\arc\object::isNull(false));
            $null = \arc\object::maybe(new CanHaveNull())->null[0];
            $this->assertTrue(\arc\object::isNull($null));
            $null = $null->getValueOr('This is null');
            $this->assertEquals('This is null', $null);

            try {
                $foo = (string)\arc\object::maybe(new CanHaveNull())->null;
            } catch (\Exception $e) {
                echo 'Jay!';
            }
        }

        function testObserve()
        {
            $log = [];
            $foo = \arc\object::observe( new CanHaveNull(), function($params) use (&$log) {
                $log[] = $params;
            });
            $foo->bar = 'foo';
            $change = array_pop($log);
            $this->assertNotNull($change);
            $this->assertEquals('bar', $change['name']);
            $this->assertEquals('add', $change['type']);

            $foo->bar = 'bar';
            $change = array_pop($log);
            $this->assertNotNull($change);
            $this->assertEquals('bar', $change['name']);
            $this->assertEquals('update', $change['type']);
            $this->assertEquals('foo', $change['oldValue']);

            unset($foo->bar);
            $change = array_pop($log);
            $this->assertNotNull($change);
            $this->assertEquals('bar', $change['name']);
            $this->assertEquals('delete', $change['type']);
            $this->assertEquals('bar', $change['oldValue']);

            $foo->changeStuff();
            $this->assertCount(2, $log);
        }

        function testGuard()
        {
//            $foo = \arc\object::seal()
        }

        function testCompose()
        {

        }

        function testProtect()
        {

        }

        function testImmutable()
        {

        }
    }

    class CanHaveNull {
        public $null = null;
        public $foo  = 'bar';
        public function getFoo()
        {
            return $this->foo;
        }
        public function doNothing()
        {
            return $this;
        }
        public function changeStuff()
        {
            $this->null = 'Not null';
            $this->foo  = 'foo';
        }
    }