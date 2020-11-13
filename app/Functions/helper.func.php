<?php

/**
 * @method Registry object_key_exists
 * @param object $obj
 * @param string $key
 * @return bool
 */
function object_key_exists(object $obj, string $key) : bool
{
    // use isset
    if (isset($obj->{$key})) return true;

    // try a loop
    foreach ($obj as $objKey => $objVal) :
        // match $objKey as against $key 
        if ($objKey === $key) return true;
    endforeach;

    // not found
    return false;
}