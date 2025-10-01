<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/LeerdoelResultaat.php';
require_once __DIR__ . '/../util/Caching/Caching.php';
require_once __DIR__ . '/../util/Constants.php';
require_once __DIR__ . '/../util/CanvasCurlCalls.php';
require_once __DIR__ . '/../util/caching/ICacheSerialisable.php';
require_once __DIR__ . '/../util/caching/MaximumRestrictions.php';
require_once __DIR__ . '/../util/caching/CourseRestricted.php';

class UncachedCanvasReader{
    private $apiKey;
    private $courseURL;
    private $baseURL;

    public function __construct($apiKey, $baseURL, $courseID) {
        $this->apiKey = $apiKey;
        $this->baseURL = $baseURL;
        $this->courseURL = "$baseURL/courses/$courseID";

        if($this->apiKey == NULL || $this->baseURL == NULL || $this->courseURL == NULL){
            throw new Exception("Invalid canvas reader created!");
        }
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
        $env = parse_ini_file(__DIR__ . '/../../.env');
        $apiKey = $env['APIKEY'];
        $baseURL = $env['baseURL'];
        $courseID = $env['courseID'];
        return new CanvasReader($apiKey, $baseURL, $courseID);
    }

    public function fetchStudentSubmissions(int $studentID){
        $url = "$this->courseURL/students/submissions?student_ids[]=$studentID&include[]=full_rubric_assessment&include[]=assignment";
        $data = curlCall($url, $this->apiKey);
        return $data;
    }

    public function fetchStudentVakbeheersing(int $studentID){
        $url = "$this->courseURL/outcome_results?user_ids[]=$studentID";
        $data = curlCall($url, $this->apiKey);
        return $data;
    }

    public function fetchSections(){
        $url = "$this->courseURL/sections";
        $data = curlCall($url, $this->apiKey);
        return $data;
    }

    public function fetchStudentsInSection($sectionID){
        $url = "$this->baseURL/sections/$sectionID/enrollments?type[]=StudentEnrollment&per_page=100";
        $data = curlCall($url, $this->apiKey);
        $data = array_map(fn($x) => $x["user"], $data);
        return $data;
    }

    public function fetchAllOutcomeGroups(){
        $url = "$this->courseURL/outcome_groups";
        $data = curlCall($url, $this->apiKey);
        return $data;
    }

    public function fetchOutcomesOfGroup( $groupID ){
        $url = "$this->courseURL/outcome_groups/$groupID/outcomes";
        $data = curlCall($url, $this->apiKey);
        $data = array_map(function($x){return $x["outcome"];}, $data);
        return $data;
    }

    public function fetchOutcome($id){
        $url = "$this->baseURL/outcomes/$id";
        try{
            $data = curlCall($url, $this->apiKey);
            return $data;
        }
        catch(Exception $e){
            if(str_contains($e->getMessage(), "The specified resource does not exist.")){
                //deleted/archived outcome
                return null;
            }
            throw $e;
        }
    }
}

class CanvasReader extends UncachedCanvasReader implements ICacheSerialisable{
    public function serialize(ICacheSerialiserVisitor $visitor): string {
        return $visitor->serializeCanvasReader($this);
    }

    /**
     * Currently no cached functions needed, as all other providers are cached. 
     * This saves unnecessary cached raw request results.
     */
}