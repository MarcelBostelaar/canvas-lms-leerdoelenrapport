<?php
require_once __DIR__ . '/../models/Leerdoel.php';
require_once __DIR__ . '/../models/LeerdoelPlanning.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../services/CanvasReader.php';
require_once __DIR__ . '/../services/LeerdoelPlanningProvider.php';
require_once __DIR__ . '/../services/StudentProvider.php';
require_once __DIR__ . '/../views/Rapport.php';

class SingleStudentViewController{
    private $studentID;

    public function __construct($studentID) {
        $this->studentID = $studentID;
    }

    public function render() {
        $CanvasReader = CanvasReader::getReader();
        $StudentReader = new StudentProvider($CanvasReader);
        $Leerdoelen = LeerdoelPlanningProvider::getPlanning($CanvasReader);
        $student = $StudentReader->getFullStudentByID($this->studentID);
        renderRapport($student, $Leerdoelen);
    }
}

$x = new SingleStudentViewController(42991); //Cursist Toetsen
$x->render();