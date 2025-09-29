<?php
require_once __DIR__ . '/../util/caching.php';

class ClearCacheController{
    public function index(){
        clearCache();
        echo "Cache cleared.";
    }
}

$x = new ClearCacheController();
$x->index();