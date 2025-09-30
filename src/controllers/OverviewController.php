<?php
require_once __DIR__ . '/../services/CanvasReader.php';
require_once __DIR__ . '/../services/GroupingProvider.php';
require_once __DIR__ . '/../views/Overview.php';

class OverviewController{
    public function index(){
        $canvasReader = CanvasReader::getReader();
        $groupings = (new GroupingProvider($canvasReader))->getSectionGroupings();

        RenderOverview($groupings);
    }
}

$x = new OverviewController();
$x->index();