<?php
require_once __DIR__ . '/../../models/Leerdoel.php';

const APIKSW = "api_keys_studentID_whitelist";
function init_cache(){
    $_SESSION['cache'] = [
        "values" => [],
        APIKSW => []
    ];
}

//Whitelisting API keys for access to specific student IDs
function checkTimeoutAPIKSW($apiKey): bool{
    if(isset($_SESSION['cache'][APIKSW][$apiKey])){
        if($_SESSION['cache'][APIKSW][$apiKey]["expires"] < time()){
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
 * @return bool. True is whitelisted, false if unknown.
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

function clearCacheForMetadata(callable $predicate){
    cache_start();
    foreach($_SESSION['cache']['values'] as $key => $entry){
        if($predicate($entry['metadata'])){
            unset($_SESSION['cache']['values'][$key]);
        }
    }
}

function clearCacheForStudentID($studentID){
    clearCacheForMetadata(function($meta) use ($studentID){
        if(is_array($meta) && isset($meta['studentID']) && $meta['studentID'] === $studentID)
            return true;
        return false;
    });
}

function getLastCacheDateForStudentID($studentID): ?DateTime{
    cache_start();
    $latest = new DateTime("1970-01-01");
    foreach($_SESSION['cache']['values'] as $entry){
        if(is_array($entry['metadata']) && isset($entry['metadata']['studentID']) && $entry['metadata']['studentID'] === $studentID){
            if($latest === null || $entry['metadata']['date'] > $latest){
                $latest = $entry['metadata']['date'];
            }
        }
    }
    return $latest;
}

function getLastCacheDateForAnyStudents(){
    cache_start();
    $latest = new DateTime("1970-01-01");
    foreach($_SESSION['cache']['values'] as $entry){
        if(is_array($entry['metadata']) && isset($entry['metadata']['studentID'])){
            if($latest === null || $entry['metadata']['date'] > $latest){
                $latest = $entry['metadata']['date'];
            }
        }
    }
    return $latest;
}

//general caching functions
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

function _set_cache($key, $value, $expireSeconds, $metadata){
    cache_start();
    $_SESSION['cache']['values'][$key] = [
        'value'=> $value,
        'expires_at'=> time() + $expireSeconds,
        'metadata' => ($metadata ?? [])
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

    //caching rules help generate key and track validity
    $key = KeyGenerator([$callingObject, $funcName], $args, $cachingRules);
    
    $data = null;
    if($cachingRules->getValidity($key)){//if rules say valid, try get from cache
        $data = get_cached($key);
        // echo "Cache " . (($data !== null) ? "hit" : "miss") . "for key $key<br>";
    }
    if($data === null){
        $data = $callback();
        if($data !== null){
            $metadata = $cachingRules->getMetaData();
            _set_cache($key, $data, $expireInSeconds, $metadata);
            //let the rule object know we succesfully retrieved and cached our item
            //rule can use this to perform additional caching work if needed
            $cachingRules->signalSuccesfullyCached(); 
        }
    }
    return $data;
}

//cache key generation
function KeyGenerator($function, $args, ICacheSerialiserVisitor $cachingRules){
    $serialized = "";
    if(is_array($function)){
        $serialized .= KeyGeneratorSingleItemHelper($function[0], $cachingRules) . $function[1];
    }
    else{
        $serialized .= KeyGeneratorSingleItemHelper($function, $cachingRules);
    }

    foreach($args as $value){
        $serialized .= KeyGeneratorSingleItemHelper($value, $cachingRules);
    }
    return md5($serialized);
}

function KeyGeneratorSingleItemHelper($item, ICacheSerialiserVisitor $visitor){
    if($item instanceof ICacheSerialisable){
        return $item->serialize($visitor);
    }
    if(is_scalar($item)){
        //Ensure uniformity in key generation when dealing with ints that might be strings other times.
        //At least for a few cases.
        if(is_bool($item)){
            return $item ? 'true' : 'false';
        }
        //int float string
        return (string)$item;
    }
    return serialize($item);
}