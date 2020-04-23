<?php


namespace Efrogg\Collection;


class CaseConverter
{
    public static function getSnakeCase($camel_case)
    {
        return preg_replace_callback(
            '/([A-Z])/',
            static function ($majuscule) {
                return '_' . strtolower($majuscule[1]);
            },
            lcfirst($camel_case)
        );
    }

    public static function getCamelCase($snake_case)
    {
        return preg_replace_callback(
            '#_(.)#',
            static function ($minuscule) {
                return strtoupper($minuscule[1]);
            },
            lcfirst($snake_case)
        );
    }
}
