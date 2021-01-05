<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Efrogg\Util;

use Doctrine\Common\Inflector\Inflector as LegacyInflector;
use Doctrine\Inflector\Inflector as InflectorObject;
use Doctrine\Inflector\InflectorFactory;

/**
 * Facade for Doctrine Inflector.
 *
 * This class allows us to maintain compatibility with Doctrine Inflector 1.3 and 2.0 at the same time.
 *
 * @internal
 */
final class Inflector
{
    /**
     * @var InflectorObject|null
     */
    private static $instance;

    /**
     * @return InflectorObject
     */
    private static function getInstance()
    {
        return (self::$instance !== null) ? self::$instance : (self::$instance = InflectorFactory::create()->build());
    }

    /**
     * @see InflectorObject::tableize()
     * @return string
     */
    public static function tableize(string $word)
    {
        return class_exists(LegacyInflector::class) ? LegacyInflector::tableize($word) : self::getInstance()->tableize($word);
    }

    /**
     * @see InflectorObject::pluralize()
     * @return string
     */
    public static function pluralize(string $word)
    {
        return class_exists(LegacyInflector::class) ? LegacyInflector::pluralize($word) : self::getInstance()->pluralize($word);
    }

    /**
     * @see InflectorObject::singularize()
     * @return string
     */
    public static function singularize(string $word)
    {
        return class_exists(LegacyInflector::class) ? LegacyInflector::singularize($word) : self::getInstance()->singularize($word);
    }

}
