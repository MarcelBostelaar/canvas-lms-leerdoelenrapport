<?php

function isSetMany($object, ...$keys): mixed{
    foreach($keys as $key){
        if(!isset($object[$key])){
            return [false, $key];
        }
    }
    return [true, null];
}

function arrayTurboFlattener(...$arrays): array{
    $result = [];
    foreach($arrays as $array){
        if(is_array($array)){
            $array = array_values($array);
            $result = array_merge($result, arrayTurboFlattener(...$array));
        } else {
            $result[] = $array;
        }
    }
    return $result;
}