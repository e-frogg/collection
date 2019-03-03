<?php
ini_set('error_reporting',E_ALL^E_NOTICE);

if(!function_exists('is_iterable')) {
    function is_iterable($var) {
        return (is_array($var) || $var instanceof Traversable);
    }
}
