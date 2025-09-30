<?php

$env = parse_ini_file(__DIR__ . '/../../.env');
$apiKey = $env['APIKEY'];
$baseURL = $env['baseURL'];
$courseID = $env['courseID'];
$sharedCacheTimeout = (int)$env['sharedCacheTimeout'];
$studentDataCacheTimeout = (int)$env['studentDataCacheTimeout'];
$roundToNearestOneOver = $env['roundToNearestOneOver'];