<?php

class StudentProvider{
    private $canvasReader;

    public function __construct(CanvasReader $canvasReader) {
        $this->canvasReader = $canvasReader;
    }

    /**
     * Returns the results per assignment for a given student.
     * @param mixed $studentID
     * @throws \Exception
     * @return array
     */
    private function getStudentResultByID($studentID){
        $data = $this->canvasReader->fetchStudentResults($studentID);
        $LeerdoelPlanning = LeerdoelPlanningProvider::getPlanning($this->canvasReader);

        $resultaten = [];
        foreach($data as $beoordelingen){
            if($beoordelingen["grade"] == null || $beoordelingen["graded_at"] == null){
                continue; //Skip ungraded
            }
            //Beoordeling gedaan
            if($beoordelingen["grade"] != "Beoordeeld"){
                continue; //Skip non-passing grades
            }
            //Alleen afgemaakte beoordelingen
            $resultaat = new LeerdoelResultaat();
            $date = new DateTime($beoordelingen["graded_at"]);
            foreach($beoordelingen["rubric_assessment"] as $rubricID => $resultDetails){
                if(isset($resultDetails["points"])){
                    //Beoordeeld leerdoel.
                    $leerdoel = $LeerdoelPlanning->getLeerdoelByCanvasID($rubricID);
                    if($leerdoel == null){
                        throw new Exception("Onbekend leerdoel met canvasID " . $rubricID . " in rubric " . $rubricID);
                    }
                    $resultaat->add($leerdoel->naam, $resultDetails["points"], $date);
                }
            }
            //Fetch assignment name
            $resultaat->beschrijving = $this->canvasReader->fetchAssignmentName($beoordelingen["assignment_id"]);
            array_push($resultaten, $resultaat);
        }
        return $resultaten;
    }

    public function getStudentMasteryByID($studentID): LeerdoelResultaat{
        $data = $this->canvasReader->fetchStudentVakbeheersing($studentID);
        $LeerdoelPlanning = LeerdoelPlanningProvider::getPlanning($this->canvasReader);

        // echo "<pre>";
        // echo var_dump($LeerdoelPlanning); // rubric details
        // echo "</pre>";
        // echo "<pre>";
        // echo json_encode($data, JSON_PRETTY_PRINT); // rubric details
        // echo "</pre>";
        $resultaat = new LeerdoelResultaat();
        $resultaat->beschrijving = "Totaal vakbeheersing";

        foreach($data["outcome_results"] as $outcome){
            $canvasID = $outcome["links"]["learning_outcome"];
            $leerdoel = $LeerdoelPlanning->getLeeruitkomstByCanvasID($canvasID);
            if($leerdoel == null){
                throw new Exception("Onbekend leerdoel met canvasID " . $canvasID . " in outcome result");
            }
            $score = $outcome["score"];
            if($score == null){
                continue; //Skip unscored outcomes
            }
            $resultaat->add($leerdoel->naam, $score, new DateTime($outcome["submitted_or_assessed_at"]));
        }
        $resultaat->fillWithZeroForMissing(array_map(fn($ld) => $ld->naam, array_merge(...array_values($LeerdoelPlanning->getAll()))));
        return $resultaat;
    }

    function getFullStudentByID($studentID) : Student{
        $student = new Student();
        $student->naam = $this->canvasReader->fetchStudentDetails($studentID)['name'];
        $student->resultaten = [$this->getStudentMasteryByID($studentID)];
        $student->resultaten = array_merge($student->resultaten, $this->getStudentResultByID($studentID));
        return $student;
    }
}