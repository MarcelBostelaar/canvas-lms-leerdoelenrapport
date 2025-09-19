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
        $student->resultaten = new LeerdoelResultaat();
        $student->resultaten->add("Naam1", Niveau::Gevorderde);
        $student->resultaten->add("Naam2", Niveau::NietBehaald);
        return $student;
    }

    public static function fromEnv() : CanvasReader {
        $apiKey = getenv('APIKEY');
        $courseURL = getenv('courseURL');
        return new CanvasReader($apiKey, $courseURL);
    }
}