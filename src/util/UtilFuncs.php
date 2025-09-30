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

function shiftArrayToRight(&$array, $fillValueGenerator = null, $positions = 1){
    if($fillValueGenerator == null){
        $fillValueGenerator = fn() => null;
    }
    for($i = 0; $i < $positions; $i++){
        array_unshift($array, $fillValueGenerator());
    }
    return $array;
}

function roundToNearestFraction(float $value, int $n): float {
    if ($n <= 0) {
        throw new InvalidArgumentException('n must be a positive integer');
    }
    $fraction = 1 / $n;
    return round($value * $fraction) / $fraction;
}