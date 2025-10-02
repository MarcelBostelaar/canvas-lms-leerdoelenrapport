<?php

class StudentIDRestricted extends CourseRestricted{
    private $id;
    private $encounteredAPIKey = null;
    private $registerAccessOnSuccess;
    /**
     * 
     * @param mixed $studentID
     * @param mixed $registerAccessOnSuccess Set this to false to avoid registering access to further data by this student if the request resolves correctly. Use this when requesting (possible) low or non-protected data about a student.
     */
    public function __construct($studentID, $registerAccessOnSuccess=true){
        $this->id = $studentID;
        $this->registerAccessOnSuccess = $registerAccessOnSuccess;
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
            throw new Exception("No api key encountered during this cache request.");
        }
        $val = canSeeStudentInfo($this->encounteredAPIKey, $this->id);
        return $val;
    }

    public function signalSuccesfullyCached(){
        if($this->encounteredAPIKey == null){
            throw new Exception("No api key encountered during this cache request");
        }
        if($this->registerAccessOnSuccess){
            whitelist_apikey_for_student_id($this->encounteredAPIKey, $this->id);
        }
    }

    public function getMetaData(): mixed{
        return [
            'studentID'=>$this->id,
            'date' => new DateTime()
        ];
    }
}