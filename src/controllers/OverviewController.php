<?php
require_once __DIR__ . '/../services/CanvasReader.php';
require_once __DIR__ . '/../services/ConfigProvider.php';
require_once __DIR__ . '/../services/GroupingProvider.php';
require_once __DIR__ . '/../views/Overview.php';
require_once __DIR__ . '/BaseController.php';

class OverviewController extends BaseController{
    public function index(){
        global $providers;
        $groupings = $providers->groupingProvider->getSectionGroupings();
        $showDate = isset($_GET["date"]) ? $_GET["date"] : "now";

        RenderOverview($groupings, new DateTime($showDate));
    }
}

$x = new OverviewController();
$x->index();