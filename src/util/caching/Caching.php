<?php
require_once __DIR__ . '/../../models/Leerdoel.php';

function clearCache(){
    //todo implement non-session
    cache_start();
    $_SESSION['cache'] = [];
}

function cache_start(){
    if(!session_id()){
        session_start();
    }
    if(!isset($_SESSION['cache'])){
        $_SESSION['cache'] = [];
    }
}

function _set_cache($key, $value, $expireSeconds){
    cache_start();
    $_SESSION['cache'][$key] = [
        'value'=> $value,
        'expires_at'=> time() + $expireSeconds
    ];
}

function get_cached($function, array $args = [], ICacheSerialiserVisitor $cachingRules){
    cache_start();
    
    $key = SerializeHelper($function, $args, $cachingRules);
    if (isset($_SESSION['cache'][$key])) {
        if ($_SESSION['cache'][$key]["expires_at"] > time()) {
            return $_SESSION['cache'][$key]["value"];
        }
        //Cache expired
        unset($_SESSION['cache'][$key]);
    }
    return null;
}

function cached_call($function, array $args = [], int $expirationDateInSeconds, ICacheSerialiserVisitor $cachingRules) {
    $data = get_cached($function, $args, $cachingRules);
    $key = SerializeHelper($function, $args, $cachingRules);
    if($data != null){
        return $data;
    }
    $data = $function(...$args);
    _set_cache(
        SerializeHelper($function, $args, $cachingRules), 
        $data, $expirationDateInSeconds);
    return $data;
}

function SerializeHelper($function, $args, ICacheSerialiserVisitor $visitor){
    $serialized = "";
    if(is_array($function)){
        $serialized .= SerializeHelperItem($function[0], $visitor) . $function[1];
    }
    else{
        $serialized .= SerializeHelperItem($function, $visitor);
    }

    foreach($args as $value){
        $serialized .= SerializeHelperItem($value, $visitor);
    }
    return $serialized;
}

function SerializeHelperItem($item, ICacheSerialiserVisitor $visitor){
    if($item instanceof ICacheSerialisable){
        return $item->serialize($visitor);
    }
    return serialize($item);
}