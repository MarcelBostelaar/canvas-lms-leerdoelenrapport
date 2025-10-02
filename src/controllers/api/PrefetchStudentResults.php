<?php

require_once __DIR__ . '/../../services/StudentProvider.php';
require_once __DIR__ . '/APIController.php';
class PrefetchStudentResults extends APIController {
    public function handle(){
        $canvasReader = $this->canvasReader;
        $studentID = (int)$_GET['id'];
        

        $student = (new StudentProvider($canvasReader))->getByID($studentID);
        $x = $student->getMasteryResults($canvasReader);
        $y = $student->getIndividualGrades($canvasReader);

        return [
            "message" => "Data prefetched"
        ];
    }
}

$x = new PrefetchStudentResults(ConfigProvider::getReader());
$x->index();