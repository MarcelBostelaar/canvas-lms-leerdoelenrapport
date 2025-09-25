<?php

function curlCall($url, $apiKey, $cacheExpiresInSeconds = 0){
    return cached_call(
        '_curlCallUncached',
        [$url, $apiKey],
        $cacheExpiresInSeconds
    );
}

/**
 * Share cached result with all users.
 */
function curlCallCrossuserCached($url, $apiKey, $cacheExpiresInSeconds = 0){
    return cached_call(
        '_curlCallUncached',
        [$url, $apiKey],
        $cacheExpiresInSeconds,
        [1] //ignore the api key in the caching, allowing for global cache of the request
    );
}

function _curlCallUncached($url, $apiKey) {
    // Initialize cURL
    $ch = curl_init($url);

    // Set headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);

    // Return response instead of outputting
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute
    $response = curl_exec($ch);

    // Handle errors
    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
    } else {
        $data = json_decode($response, true);
    }

    // Close
    curl_close($ch);
    if(isset($data["errors"])){
        $errors = "";
        foreach($data["errors"] as $message){
            $errors .= $message["message"] . "\n";
        }
        throw new Exception($errors);
    }
    return $data;
}
