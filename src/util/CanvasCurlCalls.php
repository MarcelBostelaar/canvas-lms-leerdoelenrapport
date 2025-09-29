<?php

function curlCall($url, $apiKey, $cacheExpiresInSeconds = 0, ICacheSerialiserVisitor $cachingRules){
    $data = cached_call(
        '_curlCallUncached',
        [$url, $apiKey],
        $cacheExpiresInSeconds,
        [],
        $cachingRules
    );
    // debugSearcher( "3000", $data, "<h1>Found id 3000 in curlCall for URL: $url</h1>");
    
    return $data;
}

class PaginationHeaderHandler{
    public $nextURL = null;

    public function handle($curl, $header_line){
        if (preg_match('/<([^>]*)>;\s*rel="next"/', trim($header_line), $matches)) {
            $this->nextURL = $matches[1];
        }
        return strlen($header_line);
    }
}

function _curlCallUncached($url, $apiKey) {
    // Initialize cURL
    $ch = curl_init($url);

    //Handling header reader to handle paginated results
    $nextURLHandler = new PaginationHeaderHandler();
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, [&$nextURLHandler, "handle"]);

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
        // throw new Exception("cURL Error: " . curl_error($ch));
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

    //if a next link for paginated results was found, call it recursively, append all results together.
    if($nextURLHandler->nextURL !== null){
        $additionalData = _curlCallUncached($nextURLHandler->nextURL, $apiKey);
        $data = array_merge($data, $additionalData);
    }
    return $data;
}