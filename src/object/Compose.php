<?php
/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace arc\object;

/**
 * This class creates a new object composed of all the objects passed in its constructor.
 * Any property access or method call is passed on to the last object passed in the
 * constructor that has that method or property.
 * Any property set or unset is passed to all objects that have that property.
 */
final class Compose {

    use traits\Composed;

}