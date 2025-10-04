<?php
require_once __DIR__ . '/utility/SubmissionsProcessing.php';
require_once __DIR__ . '/GroupingProvider.php';
require_once __DIR__ . '/../util/caching/StudentIDRightsAPIKeyRestricted.php';
require_once __DIR__ . '/../util/caching/Caching.php';
require_once __DIR__ . '/../util/caching/CacheRules.php';
require_once __DIR__ . '/CanvasReader.php';
require_once __DIR__ . '/LeerdoelenStructuurProvider.php';

class UncachedStudentProvider{

    /**
     * Returns the results per assignment for a given student.
     * @param mixed $studentID
     * @throws \Exception
     * @return LeerdoelResultaat[]
     */
    public function getStudentResultsByID(int $studentID): array{
        global $providers;
        $results = $providers->canvasReader->fetchStudentSubmissions($studentID);
        $leerdoelPlanning = $providers->leerdoelenStructuurProvider->getStructuur();

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

    public function getStudentMasteryByID(int $studentID): LeerdoelResultaat{
        global $providers;
        $data = $providers->canvasReader->fetchStudentVakbeheersing($studentID);
        $LeerdoelPlanning = $providers->leerdoelenStructuurProvider->getStructuur();
        
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

    function getByID(int $studentID) : Student{
        global $providers;
        return $providers->groupingProvider
                        ->getSectionGroupings()
                        ->getStudent($studentID);
    }
}

//Caching is done here
class StudentProvider extends UncachedStudentProvider{

    public function getStudentResultsByID($studentID): array{
        global $studentDataCacheTimeout;
        return cached_call(new StudentIDRightsAPIKeyRestricted($studentID), $studentDataCacheTimeout,
        fn() => parent::getStudentResultsByID($studentID),
         "getStudentResultsByID", $studentID);
    }

    public function getStudentMasteryByID($studentID): LeerdoelResultaat{
        global $studentDataCacheTimeout;
        return cached_call(new StudentIDRightsAPIKeyRestricted($studentID), $studentDataCacheTimeout,
        fn() => parent::getStudentMasteryByID($studentID),
         "getStudentMasteryByID", $studentID);
    }

    public function getByID($studentID): Student{
        global $studentDataCacheTimeout;
        return cached_call(new StudentIDRightsAPIKeyRestricted($studentID), $studentDataCacheTimeout,
        fn() => parent::getByID($studentID),
         "getByID", $studentID);
    }
}