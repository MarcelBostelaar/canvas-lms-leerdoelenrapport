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
        // echo "<pre>HIERZO";
        // var_dump($CanvasReader->fetchOutcomeLinks());
        // var_dump($CanvasReader->fetchAllOutcomeGroups());
        // var_dump($CanvasReader->fetchStudentVakbeheersing($this->studentID));
        // var_dump($CanvasReader->fetchSubmissionRubricAssessment(4088528));
        // var_dump($CanvasReader->fetchOutcome(3000));
        // echo "</pre>";
        // throw new Exception("Stop");
        $StudentReader = new StudentProvider($CanvasReader);
        $Leerdoelen = LeerdoelenStructuurProvider::getStructuur($CanvasReader);
        
        $student = $StudentReader->getFullStudentByID($this->studentID);

        renderRapport($student, $Leerdoelen);
    }
}

$x = new SingleStudentViewController(42991); //Cursist Toetsen
$x->render();