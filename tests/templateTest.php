<?php

    /*
     * This file is part of the Ariadne Component Library.
     *
     * (c) Muze <info@muze.nl>
     *
     * For the full copyright and license information, please view the LICENSE
     * file that was distributed with this source code.
     */

    class TemplateTest extends PHPUnit\Framework\TestCase
    {
        function testSimpleSubstitution()
        {
            $template = 'Hello {$someone}';
            $args = [ 'someone' => 'World!' ];
            $parsed = \arc\template::substitute( $template, $args );
            $this->assertEquals( 'Hello World!', $parsed );
        }

        function testFunctionSubstitution()
        {
            $template = 'Hello {$someone}';
            $args = [ 'someone' => function () { return 'World!'; } ];
            $parsed = \arc\template::substitute( $template, $args );
            $this->assertEquals( 'Hello World!', $parsed );
        }

        function testPartialSubstitution()
        {
            $template = 'Hello {$someone} from {$somewhere}';
            $args = [ 'someone' => 'World!' ];
            $parsed = \arc\template::substitute( $template, $args );
            $this->assertEquals( 'Hello World! from {$somewhere}', $parsed );
        }

        function testSubstituteAll()
        {
            $template = 'Hello {$someone} from {$somewhere}';
            $args = [ 'someone' => 'World!' ];
            $parsed = \arc\template::substituteAll( $template, $args );
            $this->assertEquals( 'Hello World! from ', $parsed);
        }

    }
