<?php

function curlCall($url, $apiKey, $cacheExpiresInSeconds = 0){
    $data = cached_call(
        '_curlCallUncached',
        [$url, $apiKey],
        $cacheExpiresInSeconds
    );
    // debugSearcher( "3000", $data, "<h1>Found id 3000 in curlCall for URL: $url</h1>");
    
    return $data;
}

/**
 * Share cached result with all users.
 */
function curlCallCrossuserCached($url, $apiKey, $cacheExpiresInSeconds = 0){
    $data = cached_call(
        '_curlCallUncached',
        [$url, $apiKey],
        $cacheExpiresInSeconds,
        [1] //ignore the api key in the caching, allowing for global cache of the request
    );
    // debugSearcher(3000, $data, "<h1>Found id 3000 in curlCall for URL: $url</h1>");
    return $data;
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
        $errors = "URL: $url\n";
        foreach($data["errors"] as $message){
            $errors .= $message["message"] . "\n";
        }
        throw new Exception($errors);
    }
    return $data;
}

function debugSearcher($value, $data, $message){
    //search all arrays and objects recursively
    if (is_array($data)) {
        foreach($data as $item){
            debugSearcher( $value, $item, $message);
        }
    } elseif (is_object($data)) {
        foreach ($data as $k => $v) {
            if ( $v == $value) {
                echo "Found matching value:  $value\n";
                echo $message . "\n";
                echo "<pre>";
                var_dump($data);
                echo "</pre><br><br><br>";
                return;
            }
            debugSearcher($value, $v, $message);
        }
    }
    return;
}
