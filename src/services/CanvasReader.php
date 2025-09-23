<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/LeerdoelResultaat.php';

class CanvasReader{
    private $apiKey;
    private $courseURL;
    private $masterRubric;

    public function __construct($apiKey, $courseURL, $masterRubric) {
        $this->apiKey = $apiKey;
        $this->courseURL = $courseURL;
        $this->masterRubric = $masterRubric;
    }

    private function curlCall($url) {// Initialize cURL
        $ch = curl_init($url);

        // Set headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $this->apiKey",
            "Content-Type: application/json"
        ]);

        // Return response instead of outputting
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute
        $response = curl_exec($ch);

        // Handle errors
        if (curl_errno($ch)) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            // var_dump($response); // raw response
            $data = json_decode($response, true);
        }

        // Close
        curl_close($ch);
        return $data;
    }
    
    function readStudent($studentID) : Student{
        $data = $this->fetchStudentVakbeheersing($studentID);
        echo "<pre>";
        echo json_encode($data, JSON_PRETTY_PRINT); // rubric details
        echo "</pre>";
        $data = $this->fetchStudentResults($studentID);
        echo "<pre>";
        echo json_encode($data, JSON_PRETTY_PRINT); // rubric details
        echo "</pre>";


        // Dummy data for now
        $student = new Student();
        $student->naam = $this->fetchStudentDetails($studentID)['name'];
        $newresult = new LeerdoelResultaat();
        $newresult->beschrijving = "Alles";
        array_push($student->resultaten, $newresult);
        $newresult->add("CSS", Niveau::Gevorderde, 4);
        $newresult->add("HTML", Niveau::Beginner, 2);
        
        $newresult = new LeerdoelResultaat();
        $newresult->beschrijving = "Project 1";
        array_push($student->resultaten, $newresult);
        $newresult->add("CSS", Niveau::Beginner, 3);
        $newresult->add("HTML", Niveau::NietBehaald, 1);
        return $student;
    }

    public static function getReader() : CanvasReader {
        $env = parse_ini_file('./../../.env');
        $apiKey = $env['APIKEY'];
        $courseURL = $env['courseURL'];
        $masterRubric = $env['masterRubric'];
        return new CanvasReader($apiKey, $courseURL, $masterRubric);
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

    //May be cached, not sensitive to student
    private function fetchMasterRubric(){
        $url = "$this->courseURL/rubrics/$this->masterRubric";
        $data = $this->curlCall($url);
        return $data;
    }

    private function fetchStudentResults($studentID){
        $url = "$this->courseURL/students/submissions?student_ids[]=$studentID&include[]=rubric_assessment";
        $data = $this->curlCall($url);
        return $data;
    }

    public function fetchStudentVakbeheersing($studentID){
        $url = "$this->courseURL/outcome_results";//?user_ids[]=$studentID";
        $data = $this->curlCall($url);
        return $data;
    }

    private function fetchStudentDetails($studentID){
        $url = "$this->courseURL/users/$studentID";
        $data = $this->curlCall($url);
        return $data;
    }
}