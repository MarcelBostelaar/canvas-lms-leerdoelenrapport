<?php

function isSetMany($object, ...$keys): mixed{
    foreach($keys as $key){
        if(!isset($object[$key])){
            return [false, $key];
        }
    }
    return [true, null];
}