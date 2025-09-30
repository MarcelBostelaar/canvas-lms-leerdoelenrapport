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

function get_cached($key){
    cache_start();
    
    if (isset($_SESSION['cache'][$key])) {
        if ($_SESSION['cache'][$key]["expires_at"] > time()) {
            return $_SESSION['cache'][$key]["value"];
        }
        //Cache expired
        unset($_SESSION['cache'][$key]);
    }
    return null;
}

function cached_call(ICacheSerialiserVisitor $cachingRules, int $expireInSeconds,
                        callable $callback, object|string|null $callingObject, 
                        string $funcName, mixed ...$args){
    cache_start();
    $key = SerializeHelper([$callingObject, $funcName], $args, $cachingRules);
    $data = get_cached($key);
    if($data == null){
        $data = $callback();
        _set_cache($key, $data, $expireInSeconds);
    }
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