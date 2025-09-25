<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/LeerdoelResultaat.php';
require_once __DIR__ . '/../util/caching.php';
require_once __DIR__ . '/../util/CanvasCurlCalls.php';

class CanvasReader{
    private $apiKey;
    private $courseURL;
    private $baseURL;
    private $masterRubric;

    public function __construct($apiKey, $baseURL, $courseID, $masterRubric) {
        $this->apiKey = $apiKey;
        $this->baseURL = $baseURL;
        $this->courseURL = "$baseURL/courses/$courseID";
        $this->masterRubric = $masterRubric;
    }

    public static function getReader() : CanvasReader {
        $env = parse_ini_file('./../../.env');
        $apiKey = $env['APIKEY'];
        $baseURL = $env['baseURL'];
        $courseID = $env['courseID'];
        $masterRubric = $env['masterRubric'];
        return new CanvasReader($apiKey, $baseURL, $courseID, $masterRubric);
    }

    public function fetchStrippedDownMasterRubric(){
        $data = $this->fetchMasterRubric();
        $data = $data['data'];

        $newlist = [];
        foreach ($data as $item) {
            $newlist[$item["description"]] = [
                'id' => $item['id'],
                'learning_outcome_id' => $item['learning_outcome_id']
            ];
        }
        return $newlist;
    }

    public function fetchMasterRubric(){
        $url = "$this->courseURL/rubrics/$this->masterRubric";
        
        //May be cached globally, not sensitive to student
        $data = curlCallCrossuserCached($url, $this->apiKey, 60*60*24); //cached for 1 day
        return $data;
    }

    public function fetchStudentResults($studentID){
        $url = "$this->courseURL/students/submissions?student_ids[]=$studentID&include[]=rubric_assessment";
        $data = curlCall($url, $this->apiKey, 300); //Cache for 5 minutes
        return $data;
    }

    public function fetchStudentVakbeheersing($studentID){
        $url = "$this->courseURL/outcome_results";//?user_ids[]=$studentID";
        $data = curlCall($url, $this->apiKey, 300); //Cache for 5 minutes
        return $data;
    }

    public function fetchStudentDetails($studentID){
        $url = "$this->courseURL/users/$studentID";
        $data = curlCall($url, $this->apiKey, 60*60*24); //Cache for 1 day
        return $data;
    }

    public function fetchAssignmentName($assignmentID){
        $url = "$this->courseURL/assignments/$assignmentID";
        $data = curlCallCrossuserCached($url, $this->apiKey, 60*60*24); //Cache for 1 day
        return $data["name"];
    }

    public function fetchOutcomeCalulations(){
        $url = "$this->baseURL/outcomes/3036";
        $data = curlCallCrossuserCached($url, $this->apiKey, 60*60*24); //Cache for 1 day
        return $data;
    }
}