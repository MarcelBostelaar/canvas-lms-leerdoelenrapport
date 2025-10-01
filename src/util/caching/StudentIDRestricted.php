<?php

class StudentIDRestricted extends CourseRestricted{
    private $id;
    private $encounteredAPIKey = null;
    public function __construct($studentID){
        $this->id = $studentID;
    }

    public function serializeCanvasReader(CanvasReader $reader) : string {
        $apiKey = $reader->getApiKey();
        if($this->encounteredAPIKey != null){
            if($this->encounteredAPIKey != $apiKey){
                throw new Exception("Encountered two different api keys for same request.");
            }
        }
        if($apiKey == null){
            // var_dump($reader);
            throw new Exception("No api key found in reader.");
        }
        $this->encounteredAPIKey = $apiKey;
        // echo "API key is now: ";
        // var_dump($this->encounteredAPIKey);
        // echo "<br>";
        return "CanvasReaderTest - " . $reader->getCourseURL() . " - " . $reader->getBaseURL();
    }
    public function getValidity($key): bool{
        if($this->encounteredAPIKey == null){
            throw new Exception("No api key encountered during this cache request. Key generated: $key");
        }
        $val = canSeeStudentInfo($this->encounteredAPIKey, $this->id);
        return $val;
    }

    public function signalSuccesfullyCached(){
        if($this->encounteredAPIKey == null){
            throw new Exception("No api key encountered during this cache request");
        }
        whitelist_apikey_for_student_id($this->encounteredAPIKey, $this->id);
    }
}