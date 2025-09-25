<?php
function cached_call($function, array $args = [], int $expirationDateInSeconds = 60) {
    return cached_call_sessionbased($function, $args, $expirationDateInSeconds);
    
    //APCU is not available on all systems, disabled for now


    $key = md5(serialize([$function, $args]));

    if (($result = apcu_fetch($key)) !== false) {
        return $result;
    }

    $result = $function(...$args);
    apcu_store($key, $result, $expirationDateInSeconds);

    return $result;
}

function cached_call_sessionbased($function, array $args = [], int $expirationDateInSeconds = 60) {
    if(!session_id()) session_start();
    
    if (!isset($_SESSION['cache'])) {
        // echo "New cache total";
        $_SESSION['cache'] = [];
    }
    $key = md5(serialize([$function, $args]));

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