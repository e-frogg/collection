<?php
namespace Efrogg\Collection;

use ArrayObject;

/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 29/11/16
 * Time: 08:41
 */
class ArrayObjectItem extends ArrayObject
{
    public function __get($k) {
        if(property_exists($this,$k)) {
            return $this->{$k};
        } elseif($this->offsetExists($k)) {
            return $this->offsetGet($k);
        }
        return null;
    }
    public function __set($k,$v) {
        $this->offsetSet($k,$v);
        $this->$k=$v;
    }
}