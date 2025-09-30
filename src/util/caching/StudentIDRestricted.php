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
        $this->encounteredAPIKey = $apiKey;
        return parent::serializeCanvasReader($reader);
    }
    public function getValidity(): bool{
        if($this->encounteredAPIKey == null){
            throw new Exception("No api key encountered during this cache request");
        }
        $val = canSeeStudentInfo($this->encounteredAPIKey, $this->id);
        return $val == true;
    }

    public function signalSuccesfullyCached(){
        if($this->encounteredAPIKey == null){
            throw new Exception("No api key encountered during this cache request");
        }
        whitelist_apikey_for_student_id($this->encounteredAPIKey, $this->id);
    }
}