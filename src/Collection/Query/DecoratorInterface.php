<?php
/**
 * Created by PhpStorm.
 * User: raph
 * Date: 24/11/16
 * Time: 05:15
 */

namespace Efrogg\Collection\Query;


interface DecoratorInterface
{
    public function decorate($query);

}