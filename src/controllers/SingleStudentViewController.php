<?php
require_once __DIR__ . '/../models/Leerdoel.php';
require_once __DIR__ . '/../models/LeerdoelenStructuur.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../services/CanvasReader.php';
require_once __DIR__ . '/../services/LeerdoelenStructuurProvider.php';
require_once __DIR__ . '/../services/StudentProvider.php';
require_once __DIR__ . '/../services/ConfigProvider.php';
require_once __DIR__ . '/../views/Rapport.php';
require_once __DIR__ . '/BaseController.php';

class SingleStudentViewController extends BaseController{

    public function render() {
        global $providers;
        
        if(!isset($_GET['id'])){
            throw new Exception("No id provided");
        }
        $studentID = intval($_GET['id']);
        $StudentReader = $providers->studentProvider;
        $Leerdoelen = $providers->leerdoelenStructuurProvider->getStructuur();
        $student = $StudentReader->getByID($studentID);
        $mastery = $student->getMasteryResults();
        $grades = $student->getIndividualGrades();
        if(count($grades) > 0){
            $uitkomsten = array_merge([$mastery], $grades);
        }
        else{
            $uitkomsten = [$mastery];
        }
        // echo "<pre>";
        // var_dump($uitkomsten);
        // echo "</pre>";
        renderRapport($student, $Leerdoelen, $uitkomsten);
    }
}

$x = new SingleStudentViewController();
$x->render();