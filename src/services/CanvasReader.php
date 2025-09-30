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
        $env = parse_ini_file('./../../.env');
        $apiKey = $env['APIKEY'];
        $baseURL = $env['baseURL'];
        $courseID = $env['courseID'];
        return new CanvasReader($apiKey, $baseURL, $courseID);
    }

    public function fetchStudentSubmissions($studentID){
        $url = "$this->courseURL/students/submissions?student_ids[]=$studentID&include[]=full_rubric_assessment&include[]=assignment";
        $data = curlCall($url, $this->apiKey);
        return $data;
    }

    public function fetchStudentVakbeheersing($studentID){
        $url = "$this->courseURL/outcome_results?user_ids[]=$studentID";
        $data = curlCall($url, $this->apiKey);
        return $data;
    }

    public function fetchStudentDetails($studentID){
        $url = "$this->courseURL/users/$studentID";
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

    public static function getReader() : CanvasReader{
        global $sharedCacheTimeout;
        return cached_call(new CourseRestricted(), $sharedCacheTimeout,
        fn() => UncachedCanvasReader::getReader(), self::class,
        "getReader");
    }

    public function fetchStudentSubmissions($studentID){
        global $studentDataCacheTimeout;
        return cached_call(new StudentIDRestricted($studentID), $studentDataCacheTimeout,
        fn() => parent::fetchStudentSubmissions($studentID), $this,
        "fetchStudentSubmissions", $studentID);
    }
    public function fetchStudentVakbeheersing($studentID){
        global $studentDataCacheTimeout;
        return cached_call(new StudentIDRestricted($studentID), $studentDataCacheTimeout,
        fn() => parent::fetchStudentVakbeheersing($studentID), $this,
        "fetchStudentVakbeheersing", $studentID);
    }
    public function fetchStudentDetails($studentID){
        global $studentDataCacheTimeout;
        return cached_call(new StudentIDRestricted($studentID), $studentDataCacheTimeout,
        fn() => parent::fetchStudentDetails($studentID), $this,
        "fetchStudentDetails", $studentID);
    }

    public function fetchSections(){
        //Cached to maximum restriction, to ensure teachers only have access to students in their own sections.
        global $studentDataCacheTimeout;
        return cached_call(new MaximumRestrictions(), $studentDataCacheTimeout,
        fn() => parent::fetchSections(), $this,
        "fetchSections");
    }

    public function fetchStudentsInSection($sectionID){
        //Cached to maximum restriction, to ensure teachers only have access to students in their own sections.
        global $studentDataCacheTimeout;
        return cached_call(new MaximumRestrictions(), $studentDataCacheTimeout,
        fn() => parent::fetchStudentsInSection($sectionID), $this,
        "fetchStudentsInSection", $sectionID);
    }

    public function fetchAllOutcomeGroups(){
        global $sharedCacheTimeout;
        return cached_call(new CourseRestricted(), $sharedCacheTimeout,
        fn() => parent::fetchAllOutcomeGroups(), $this,
        "fetchAllOutcomeGroups");
    }
    
    public function fetchOutcomesOfGroup($groupID){
        global $sharedCacheTimeout;
        return cached_call(new CourseRestricted(), $sharedCacheTimeout,
        fn() => parent::fetchOutcomesOfGroup($groupID), $this,
        "fetchOutcomesOfGroup", $groupID);
    }
    public function fetchOutcome($id){
        global $sharedCacheTimeout;
        return cached_call(new CourseRestricted(), $sharedCacheTimeout,
        fn() => parent::fetchOutcome($id), $this,
        "fetchOutcome", $id);
    }
}