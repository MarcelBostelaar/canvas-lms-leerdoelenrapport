<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/LeerdoelResultaat.php';

class CanvasReader{
    private $apiKey;
    private $courseURL;

    public function __construct($apiKey, $courseURL) {
        $this->apiKey = $apiKey;
        $this->courseURL = $courseURL;
    }
    
    function readStudent($studentID) : Student{
        // Dummy data for now
        $student = new Student();
        $student->naam = "Jan Jansen";
        $newresult = new LeerdoelResultaat();
        $newresult->beschrijving = "Alles";
        array_push($student->resultaten, $newresult);
        $newresult->add("Naam1", Niveau::Gevorderde, 4);
        $newresult->add("Naam2", Niveau::Beginner, 2);
        return $student;
    }

    public static function fromEnv() : CanvasReader {
        $apiKey = getenv('APIKEY');
        $courseURL = getenv('courseURL');
        return new CanvasReader($apiKey, $courseURL);
    }
}