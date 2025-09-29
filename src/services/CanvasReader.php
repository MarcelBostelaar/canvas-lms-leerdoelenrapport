<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/LeerdoelResultaat.php';
require_once __DIR__ . '/../util/caching.php';
require_once __DIR__ . '/../util/Constants.php';
require_once __DIR__ . '/../util/CanvasCurlCalls.php';

class CanvasReader{
    private $apiKey;
    private $courseURL;
    private $baseURL;

    public function __construct($apiKey, $baseURL, $courseID) {
        $this->apiKey = $apiKey;
        $this->baseURL = $baseURL;
        $this->courseURL = "$baseURL/courses/$courseID";
    }

    public static function getReader() : CanvasReader {
        global $sharedCacheTimeout;
        return cached_call(
            [self::class, '_getReader'],
         [],
         $sharedCacheTimeout);
    }

    public static function _getReader() : CanvasReader {
        $env = parse_ini_file('./../../.env');
        $apiKey = $env['APIKEY'];
        $baseURL = $env['baseURL'];
        $courseID = $env['courseID'];
        return new CanvasReader($apiKey, $baseURL, $courseID);
    }

    public function fetchStudentSubmissions($studentID){
        //TODO these assessments are also paginated. Fix by making them seperate calls per submission.
        $url = "$this->courseURL/students/submissions?student_ids[]=$studentID&include[]=full_rubric_assessment&include[]=assignment";
        $data = curlCall($url, $this->apiKey, 300); //Cache for 5 minutes
        return $data;
    }

    // public function fetchSubmissionRubricAssessment($submissionID){
    //     $url = "$this->courseURL/submissions/$submissionID?include[]=rubric";
    //     $data = curlCall($url, $this->apiKey, 300); //Cache for 5 minutes
    //     return $data;
    // }

    public function fetchStudentVakbeheersing($studentID){
        $url = "$this->courseURL/outcome_results?user_ids[]=$studentID";
        $data = curlCall($url, $this->apiKey, 300); //Cache for 5 minutes
        return $data;
    }

    public function fetchStudentDetails($studentID){
        $url = "$this->courseURL/users/$studentID";
        $data = curlCall($url, $this->apiKey, 60*60*24); //Cache for 1 day
        return $data;
    }

    // public function fetchAssignmentName($assignmentID){
    //     $url = "$this->courseURL/assignments/$assignmentID";
    //     $data = curlCallCrossuserCached($url, $this->apiKey, 60*60*24); //Cache for 1 day
    //     return $data["name"];
    // }

    public function fetchAllOutcomeGroups(){
        $url = "$this->courseURL/outcome_groups";
        $data = curlCallCrossuserCached($url, $this->apiKey, 60*60*24); //Cache for 1 day
        return $data;
    }

    public function fetchOutcomesOfGroup( $groupID ){
        $url = "$this->courseURL/outcome_groups/$groupID/outcomes";
        $data = curlCallCrossuserCached($url, $this->apiKey, 60*60*24); //Cache for 1 day
        // echo "<pre>";
        // var_dump($data);
        // echo "</pre>";
        // throw new Exception("Stop");
        $data = array_map(function($x){return $x["outcome"];}, $data);
        return $data;
    }

    public function fetchOutcome($id){
        $url = "$this->baseURL/outcomes/$id";
        try{
            $data = curlCallCrossuserCached($url, $this->apiKey, 60*60*24); //Cache for 1 day
        }
        catch(Exception $e){
            if(str_contains($e->getMessage(), "The specified resource does not exist.")){
                //Possibly deleted/archived outcome
                return null;
            }
            throw $e;
        }
        $data = curlCallCrossuserCached($url, $this->apiKey, 60*60*24); //Cache for 1 day
        return $data;
    }

    // public function fetchOutcomeLinks(){
    //     $url = "$this->courseURL/outcome_group_links";
    //     $data = curlCallCrossuserCached($url, $this->apiKey, 60*60*24); //Cache for 1 day
    //     return $data;
    // }

    // public function fetchTest($accountID){
    //     $url = "$this->baseURL/accounts/$accountID/outcome_groups";//?include[]=outcomes&include[]=subgroups";
    //     $data = curlCall($url, $this->apiKey, 300); //Cache for 5 minutes
    //     return $data;
    // }
}