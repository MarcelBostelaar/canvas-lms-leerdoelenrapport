<?php
require_once __DIR__ . '/utility/SubmissionsProcessing.php';
require_once __DIR__ . '/GroupingProvider.php';

class StudentProvider implements ICacheSerialisable{
    
    private $canvasReader;

    public function __construct(CanvasReader $canvasReader) {
        $this->canvasReader = $canvasReader;
    }

    public function serialize(ICacheSerialiserVisitor $visitor): string {
        return "StudentProvider - ". $visitor->serializeCanvasReader(reader: $this->canvasReader);
    }

    /**
     * Returns the results per assignment for a given student.
     * @param mixed $studentID
     * @throws \Exception
     * @return LeerdoelResultaat[]
     */
    public function getStudentResultsByID($studentID): array{
        $results = $this->canvasReader->fetchStudentSubmissions($studentID);
        $leerdoelPlanning = (new LeerdoelenStructuurProvider($this->canvasReader))->getStructuur();

        //filter needed info to structs
        $output = [];
        foreach($results as $result){
            if($result["workflow_state"] != "graded"){
                continue; //Skip ungraded submissions
            }

            $assessmentResults = [];
            foreach($result["full_rubric_assessment"]["data"] as $assesment){
                if(!isset($assesment["points"])){
                    continue;
                }
                $newAssessment = new AssessmentStruct($assesment["points"], $assesment["learning_outcome_id"]);
                array_push($assessmentResults, $newAssessment);
            }

            $filteredInfo = new SubmissionStruct(
                $result["assignment"]["name"],
                strtotime($result["graded_at"]),
                $assessmentResults
            );

            //finished filtering
            $newResultaat = new LeerdoelResultaat();
            $newResultaat->beschrijving = $filteredInfo->assignmentName;
            foreach($filteredInfo->Assessment as $assessment){
                $leerdoel = $leerdoelPlanning->getLeeruitkomstByCanvasID($assessment->learning_outcome_id);
                if($leerdoel == null){
                    // echo "<span style='color: red'>Onbekend leerdoel met ID " . $assessment->learning_outcome_id . "</span><br>";
                    continue;
                }
                $newResultaat->add($leerdoel, $assessment->score, $filteredInfo->gradedAt);
            }
            array_push($output, $newResultaat);
        }
        return $output;
    }

    public function getStudentMasteryByID($studentID): LeerdoelResultaat{
        $data = $this->canvasReader->fetchStudentVakbeheersing($studentID);
        $LeerdoelPlanning = (new LeerdoelenStructuurProvider($this->canvasReader))->getStructuur();
        
        $resultaat = new LeerdoelResultaat();
        $resultaat->beschrijving = "Totaal vakbeheersing";


        foreach($data["outcome_results"] as $outcome){
            $canvasID = $outcome["links"]["learning_outcome"];
            $leerdoel = $LeerdoelPlanning->getLeeruitkomstByCanvasID($canvasID);
            if($leerdoel == null){
                // echo "<span style='color: red'>Onbekend leerdoel met ID " . $canvasID . "</span><br>";
                continue;
                // throw new Exception("Onbekend leerdoel met canvasID " . $canvasID . " in outcome result");
            }
            $score = $outcome["score"];
            if($score == null){
                continue; //Skip unscored outcomes
            }
            $resultaat->add($leerdoel, $score, new DateTime($outcome["submitted_or_assessed_at"]));
        }
        $resultaat->fillWithZeroForMissing($LeerdoelPlanning->getAllLeerdoelen());
        return $resultaat;
    }

    function getByID($studentID) : Student{
        return (new GroupingProvider($this->canvasReader))
                        ->getSectionGroupings()
                        ->getStudent($studentID, $this->canvasReader);
    }
}