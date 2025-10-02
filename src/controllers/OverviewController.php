<?php
require_once __DIR__ . '/../services/CanvasReader.php';
require_once __DIR__ . '/../services/ConfigProvider.php';
require_once __DIR__ . '/../services/GroupingProvider.php';
require_once __DIR__ . '/../views/Overview.php';

class OverviewController{
    public function index(CanvasReader $canvasReader){
        $groupings = (new GroupingProvider($canvasReader))->getSectionGroupings();

        RenderOverview($groupings);
    }
}

$x = new OverviewController();
$x->index(ConfigProvider::getReader());