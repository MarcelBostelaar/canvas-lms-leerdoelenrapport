<?php
require_once __DIR__ . '/../../models/Leerdoel.php';

const APIKSW = "api_keys_studentID_whitelist";

function init_cache(){
    $_SESSION['cache'] = [
        "values" => [],
        APIKSW => []
    ];
}

function checkTimeoutAPIKSW($apiKey): bool{
    if(isset($_SESSION['cache'][APIKSW][$apiKey])){
        if($_SESSION['cache'][APIKSW][$apiKey]["expires"] > time()){
            //expired
            unset($_SESSION['cache'][APIKSW][$apiKey]);
            return false;
        }
        return true;
    }
    return false;
}

function whitelist_apikey_for_student_id(string $apiKey, int $studentID){
    global $studentDataCacheTimeout;
    cache_start();
    checkTimeoutAPIKSW($apiKey);
    if(!isset($_SESSION['cache'][APIKSW][$apiKey])){
        $_SESSION['cache'][APIKSW][$apiKey] = [
            "expires" => time() + $studentDataCacheTimeout,
            "ids" => []
        ];
    }
    $_SESSION['cache'][APIKSW][$apiKey]["ids"][$studentID] = true;
}

/**
 * Summary of canSeeStudentInfo
 * @param mixed $apiKey
 * @param mixed $studentID
 * @return bool | null. True is whitelisted, false if blacklisted, null is unknown.
 */
function canSeeStudentInfo($apiKey, $studentID): bool{
    cache_start();
    checkTimeoutAPIKSW($apiKey);
    if(isset($_SESSION['cache'][APIKSW][$apiKey])){
        if(isset($_SESSION['cache'][APIKSW][$apiKey]["ids"][$studentID])){
            return $_SESSION['cache'][APIKSW][$apiKey]["ids"][$studentID];
        }
    }
    return false;
}

function clearCache(){
    //todo implement non-session
    cache_start();
    init_cache();
}

function cache_start(){
    if(!session_id()){
        session_start();
    }
    if(!isset($_SESSION['cache'])){
        init_cache();
    }
}

function _set_cache($key, $value, $expireSeconds){
    cache_start();
    $_SESSION['cache']['values'][$key] = [
        'value'=> $value,
        'expires_at'=> time() + $expireSeconds
    ];
}

function get_cached($key){
    cache_start();
    
    if (isset($_SESSION['cache']['values'][$key])) {
        if ($_SESSION['cache']['values'][$key]["expires_at"] > time()) {
            return $_SESSION['cache']['values'][$key]["value"];
        }
        //Cache expired
        unset($_SESSION['cache']['values'][$key]);
    }
    return null;
}

function cached_call(ICacheSerialiserVisitor $cachingRules, int $expireInSeconds,
                        callable $callback, object|string|null $callingObject, 
                        string $funcName, mixed ...$args){
    cache_start();
    
    $key = SerializeHelper([$callingObject, $funcName], $args, $cachingRules);
    
    $data = null;
    if($cachingRules->getValidity()){
        $data = get_cached($key);
    }
    if($data == null){
        $data = $callback();
        if($data != null){
            _set_cache($key, $data, $expireInSeconds);
            $cachingRules->signalSuccesfullyCached();
        }
    }
    return $data;
}

function SerializeHelper($function, $args, ICacheSerialiserVisitor $cachingRules){
    $serialized = "";
    if(is_array($function)){
        $serialized .= SerializeHelperItem($function[0], $cachingRules) . $function[1];
    }
    else{
        $serialized .= SerializeHelperItem($function, $cachingRules);
    }

    foreach($args as $value){
        $serialized .= SerializeHelperItem($value, $cachingRules);
    }
    return $serialized;
}

function SerializeHelperItem($item, ICacheSerialiserVisitor $visitor){
    if($item instanceof ICacheSerialisable){
        return $item->serialize($visitor);
    }
    return serialize($item);
}