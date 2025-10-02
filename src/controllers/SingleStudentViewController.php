<?php
require_once __DIR__ . '/../models/Leerdoel.php';
require_once __DIR__ . '/../models/LeerdoelenStructuur.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../services/CanvasReader.php';
require_once __DIR__ . '/../services/LeerdoelenStructuurProvider.php';
require_once __DIR__ . '/../services/StudentProvider.php';
require_once __DIR__ . '/../services/ConfigProvider.php';
require_once __DIR__ . '/../views/Rapport.php';

class SingleStudentViewController{
    private int $studentID;
    private CanvasReader $canvasReader;

    public function __construct(CanvasReader $canvasReader) {
        $this->canvasReader = $canvasReader;
    }

    public function render() {
        
        if(!isset($_GET['id'])){
            throw new Exception("No id provided");
        }
        $studentID = intval($_GET['id']);
        $StudentReader = new StudentProvider($this->canvasReader);
        $Leerdoelen = (new LeerdoelenStructuurProvider($this->canvasReader))->getStructuur();
        $student = $StudentReader->getByID($studentID);
        $mastery = $student->getMasteryResults($this->canvasReader);
        $grades = $student->getIndividualGrades($this->canvasReader);
        if(count($grades) > 1){
            $uitkomsten = array_merge([$mastery], $grades);
        }
        else{
            $uitkomsten = [$mastery];
        }
        renderRapport($student, $Leerdoelen, $uitkomsten);
    }
}

$x = new SingleStudentViewController(ConfigProvider::getReader());
$x->render();