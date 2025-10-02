<?php

require_once __DIR__ . '/../services/CanvasReader.php';
require_once __DIR__ . '/../services/GroupingProvider.php';
require_once __DIR__ . '/../services/ConfigProvider.php';

class TestController{
    public function index(CanvasReader $canvasReader){
        // $canvasReader = ConfigProvider::getReader();
        $studentID = 42991; //Cursist Toetsen
        // $canvasReader->fetchStudentSections($studentID);
        // $sections = (new GroupingProvider($canvasReader))->getSectionGroupings();
        $sections = (new GroupingProvider($canvasReader))->getSectionGroupings();


        echo "<pre>";
        // var_dump($canvasReader->fetchSections());
        // var_dump($canvasReader->fetchStudentsInSection(sectionID: 55355));


        var_dump($sections);
        echo "</pre>";
    }
}

$x = new TestController();
$x->index(ConfigProvider::getReader());