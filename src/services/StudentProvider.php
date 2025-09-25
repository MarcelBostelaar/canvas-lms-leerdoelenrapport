<?php

class StudentProvider{
    private $canvasReader;

    public function __construct(CanvasReader $canvasReader) {
        $this->canvasReader = $canvasReader;
    }

    private function getStudentResultByID($studentID){
        // $data = $this->fetchStudentVakbeheersing($studentID);
        // echo "<pre>";
        // echo json_encode($data, JSON_PRETTY_PRINT); // rubric details
        // echo "</pre>";
        $data = $this->canvasReader->fetchStudentResults($studentID);
        echo "<pre>";
        echo json_encode($data, JSON_PRETTY_PRINT); // rubric details
        echo "</pre>";

        $LeerdoelPlanning = LeerdoelPlanningProvider::getPlanning($this->canvasReader);

        //DEBUG
        foreach($LeerdoelPlanning->getAll() as $categorie => $leerdoelen){
            foreach($leerdoelen as $leerdoel){
                echo "Leerdoel: " . $leerdoel->naam . " met canvasID " . $leerdoel->id_in_canvas . "<br>";
            }
        }
        //END DEBUG

        $resultaten = [];
        foreach($data as $beoordelingen){
            if($beoordelingen["grade"] != null && $beoordelingen["graded_at"] != null){
                //Beoordeling gedaan
                if($beoordelingen["grade"] == "Beoordeeld"){
                    //Alleen afgemaakte beoordelingen
                    $resultaat = new LeerdoelResultaat();
                    $date = new DateTime($beoordelingen["graded_at"]);
                    foreach($beoordelingen["rubric_assessment"] as $rubricID => $rubricDetails){
                        $ratingID = $rubricDetails["rating_id"];
                        if(isset($rubricDetails["points"])){
                            //Beoordeeld leerdoel.
                            $leerdoel = $LeerdoelPlanning->getLeerdoelByCanvasID($ratingID);
                            if($leerdoel == null){
                                throw new Exception("Onbekend leerdoel met canvasID " . $ratingID . " in rubric " . $rubricID);
                            }
                            $resultaat->add($leerdoel->naam, Niveau::from($rubricDetails["points"]), $date);
                        }
                    }
                    array_push($resultaten, $resultaat);
                    //TODO opdrachtnaam nog ergens ophalen
                }
            }
        }
        return $resultaten;
    }

    function getFullStudentByID($studentID) : Student{
        $student = new Student();
        $student->naam = $this->canvasReader->fetchStudentDetails($studentID)['name'];
        $student->resultaten = $this->getStudentResultByID($studentID);
        return $student;
    }
}