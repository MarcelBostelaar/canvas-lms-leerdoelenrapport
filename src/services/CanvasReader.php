<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/LeerdoelResultaat.php';
require_once __DIR__ . '/../util/Caching/Caching.php';
require_once __DIR__ . '/../util/Constants.php';
require_once __DIR__ . '/../util/CanvasCurlCalls.php';
require_once __DIR__ . '/../util/caching/ICacheSerialisable.php';
require_once __DIR__ . '/../util/caching/MaximumRestrictions.php';
require_once __DIR__ . '/../util/caching/CourseRestricted.php';

class CanvasReader extends ICacheSerialisable{
    private $apiKey;
    private $courseURL;
    private $baseURL;

    public function __construct($apiKey, $baseURL, $courseID) {
        $this->apiKey = $apiKey;
        $this->baseURL = $baseURL;
        $this->courseURL = "$baseURL/courses/$courseID";
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function getCourseURL(){
        return $this->courseURL;
    }

    public function getBaseURL(){
        return $this->baseURL;
    }

    public static function getReader() : CanvasReader {
        global $sharedCacheTimeout;
        return cached_call(
            [self::class, '_getReader'],
         [],
         $sharedCacheTimeout,
        new MaximumRestrictions());
    }

    public static function _getReader() : CanvasReader {
        $env = parse_ini_file('./../../.env');
        $apiKey = $env['APIKEY'];
        $baseURL = $env['baseURL'];
        $courseID = $env['courseID'];
        return new CanvasReader($apiKey, $baseURL, $courseID);
    }

    public function fetchStudentSubmissions($studentID){
        global $studentDataCacheTimeout;
        //TODO these assessments are also paginated. Fix by making them seperate calls per submission.
        $url = "$this->courseURL/students/submissions?student_ids[]=$studentID&include[]=full_rubric_assessment&include[]=assignment";
        $data = curlCall($url, $this->apiKey, $studentDataCacheTimeout, new MaximumRestrictions());
        return $data;
    }

    public function fetchStudentVakbeheersing($studentID){
        global $studentDataCacheTimeout;
        $url = "$this->courseURL/outcome_results?user_ids[]=$studentID";
        $data = curlCall($url, $this->apiKey, $studentDataCacheTimeout, new MaximumRestrictions());
        return $data;
    }

    public function fetchStudentDetails($studentID){
        global $sharedCacheTimeout;
        $url = "$this->courseURL/users/$studentID";
        $data = curlCall($url, $this->apiKey, $sharedCacheTimeout, new MaximumRestrictions()); //Cache for 1 day
        return $data;
    }

    public function fetchAllOutcomeGroups(){
        global $sharedCacheTimeout;
        $url = "$this->courseURL/outcome_groups";
        $data = curlCall($url, $this->apiKey, $sharedCacheTimeout, new CourseRestricted());
        return $data;
    }

    public function fetchOutcomesOfGroup( $groupID ){
        global $sharedCacheTimeout;
        $url = "$this->courseURL/outcome_groups/$groupID/outcomes";
        $data = curlCall($url, $this->apiKey, $sharedCacheTimeout, new CourseRestricted()); //Cache for 1 day
        // echo "<pre>";
        // var_dump($data);
        // echo "</pre>";
        // throw new Exception("Stop");
        $data = array_map(function($x){return $x["outcome"];}, $data);
        return $data;
    }

    public function fetchOutcome($id){
        global $sharedCacheTimeout;
        $url = "$this->baseURL/outcomes/$id";
        try{
            $data = curlCall($url, $this->apiKey, $sharedCacheTimeout, new CourseRestricted()); //Cache for 1 day
            return $data;
        }
        catch(Exception $e){
            if(str_contains($e->getMessage(), "The specified resource does not exist.")){
                //Possibly deleted/archived outcome
                return null;
            }
            throw $e;
        }
    }
}