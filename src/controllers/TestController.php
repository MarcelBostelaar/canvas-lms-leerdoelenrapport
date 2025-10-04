<?php

require_once __DIR__ . '/../services/CanvasReader.php';
require_once __DIR__ . '/../services/GroupingProvider.php';
require_once __DIR__ . '/../services/ConfigProvider.php';
require_once __DIR__ . '/BaseController.php';

class TestController extends BaseController{
    public function index(){
        global $providers;
        // $canvasReader = ConfigProvider::getReader();
        $studentID = 42991; //Cursist Toetsen
        // $canvasReader->fetchStudentSections($studentID);
        // $sections = (new GroupingProvider($canvasReader))->getSectionGroupings();
        $sections = $providers->groupingProvider->getSectionGroupings();


        echo "<pre>";
        // var_dump($canvasReader->fetchSections());
        // var_dump($canvasReader->fetchStudentsInSection(sectionID: 55355));


        var_dump($sections);
        echo "</pre>";
    }
}

$x = new TestController();
$x->index();