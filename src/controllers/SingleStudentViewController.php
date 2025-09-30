<?php
require_once __DIR__ . '/../models/Leerdoel.php';
require_once __DIR__ . '/../models/LeerdoelenStructuur.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../services/CanvasReader.php';
require_once __DIR__ . '/../services/LeerdoelenStructuurProvider.php';
require_once __DIR__ . '/../services/StudentProvider.php';
require_once __DIR__ . '/../views/Rapport.php';

class SingleStudentViewController{
    private $studentID;

    public function __construct($studentID) {
        $this->studentID = $studentID;
    }

    public function render() {
        $CanvasReader = CanvasReader::getReader();
        $CanvasReader = new CanvasReader($CanvasReader->getApiKey(), $CanvasReader->getBaseURL(), 70126);
        $StudentReader = new StudentProvider($CanvasReader);
        $Leerdoelen = (new LeerdoelenStructuurProvider($CanvasReader))->getStructuur();
        $student = $StudentReader->getByID($this->studentID);
        $mastery = $student->getMasteryResults($CanvasReader);
        $grades = $student->getIndividualGrades($CanvasReader);
        if(count($grades) > 1){
            $uitkomsten = array_merge([$mastery], $grades);
        }
        else{
            $uitkomsten = [$mastery];
        }
        renderRapport($student, $Leerdoelen, $uitkomsten);
    }

    public static function CreateFromGET() : SingleStudentViewController {
        if(!isset($_GET['studentID'])){
            throw new Exception("No studentID provided");
        }
        $studentID = intval($_GET['studentID']);
        return new SingleStudentViewController($studentID);
    }
}

$x = SingleStudentViewController::CreateFromGET();
$x->render();