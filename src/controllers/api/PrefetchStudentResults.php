<?php

require_once __DIR__ . '/../../services/StudentProvider.php';
require_once __DIR__ . '/APIController.php';
class PrefetchStudentResults extends APIController {
    public function handle(){
        global $providers;
        $studentID = (int)$_GET['id'];
        

        $student = $providers->studentProvider->getByID($studentID);
        $x = $student->getMasteryResults();
        $y = $student->getIndividualGrades();

        return [
            "message" => "Data prefetched"
        ];
    }
}

$x = new PrefetchStudentResults();
$x->index();