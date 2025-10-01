<?php

require_once __DIR__ . '/../../services/CanvasReader.php';
require_once __DIR__ . '/../../services/StudentProvider.php';
require_once __DIR__ . '/APIController.php';
class PrefetchStudentResults extends APIController {
    protected $debug_keep_output = true;
    public function handle(){
        $canvasReader = CanvasReader::getReader();
        $studentID = (int)$_GET['id'];
        
        // var_dump($canvasReader);
        $student = (new StudentProvider($canvasReader))->getByID($studentID);
        $x = $student->getMasteryResults($canvasReader);
        $y = $student->getIndividualGrades($canvasReader);
        // return "Hello";
        return [
            "message" => "Data prefetched",
            "values" => [serialize($x), serialize($y)]
        ];
    }
}

$x = new PrefetchStudentResults();
$x->index();