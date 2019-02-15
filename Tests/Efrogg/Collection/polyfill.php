<?php

if(!function_exists('is_iterable')) {
    function is_iterable($var) {
        return (is_array($var) || $var instanceof Traversable);
    }
}
