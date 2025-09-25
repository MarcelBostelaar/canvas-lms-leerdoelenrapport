<?php
function cached_call($function, array $args = [], int $expirationDateInSeconds = 60, $ignoreKeysInPos = []) {
    return cached_call_sessionbased($function, $args, $expirationDateInSeconds, $ignoreKeysInPos);
    
    //APCU is not available on all systems, disabled for now
    
    $newKeyArgs = [];
    for ($i = 0; $i < count($args); $i++){
        if (!in_array($i, $ignoreKeysInPos)) {
            array_push($newKeyArgs, $args[$i]);
        }
    }

    $key = md5(serialize([$function, $newKeyArgs]));

    if (($result = apcu_fetch($key)) !== false) {
        return $result;
    }

    $result = $function(...$args);
    apcu_store($key, $result, $expirationDateInSeconds);

    return $result;
}

function cached_call_sessionbased($function, array $args = [], int $expirationDateInSeconds = 60, $ignoreKeysInPos = []) {
    if(!session_id()) session_start();
    
    if (!isset($_SESSION['cache'])) {
        // echo "New cache total";
        $_SESSION['cache'] = [];
    }

    $newKeyArgs = [];
    for ($i = 0; $i < count($args); $i++){
        if (!in_array($i, $ignoreKeysInPos)) {
            array_push($newKeyArgs, $args[$i]);
        }
    }
    
    $key = md5(serialize([$function, $newKeyArgs]));

    if (isset($_SESSION['cache'][$key])) {
        if ($_SESSION['cache'][$key]["expires_at"] > time()) {
            // echo "Returning cached value";
            return $_SESSION['cache'][$key]["value"];
        }
        //Cache expired
        unset($_SESSION['cache'][$key]);
        // echo "Cache expired";
    }

    $result = call_user_func_array($function, $args);

    $_SESSION["cache"][$key] = [
        "value" => $result,
        "expires_at" => time() + $expirationDateInSeconds
    ];
    // echo "No cached result available";
    return $result;
}