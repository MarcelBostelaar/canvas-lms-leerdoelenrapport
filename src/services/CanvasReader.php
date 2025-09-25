<?php

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/LeerdoelResultaat.php';
require_once __DIR__ . '/../util/caching.php';

function curlCall($url, $apiKey, $cacheExpiresInSeconds = 0){
    return cached_call(
        '_curlCallUncached',
        [$url, $apiKey],
        $cacheExpiresInSeconds
    );
}

function _curlCallUncached($url, $apiKey) {
    var_dump($url);
    echo "<br>";
    var_dump($apiKey);
    echo "<br>";

    // Initialize cURL
    $ch = curl_init($url);

    // Set headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
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
    if(isset($data["errors"])){
        $errors = "";
        foreach($data["errors"] as $message){
            $errors .= $message["message"] . "\n";
        }
        throw new Exception($errors);
    }
    return $data;
}

//May be cached, not sensitive to student
function _fetchMasterRubricInternalUncached($courseURL, $apiKey, $masterRubricID){
    $url = "$courseURL/rubrics/$masterRubricID";
    $data = curlCall($url, $apiKey);
    return $data;
}

class CanvasReader{
    private $apiKey;
    private $courseURL;
    private $masterRubric;

    public function __construct($apiKey, $courseURL, $masterRubric) {
        $this->apiKey = $apiKey;
        $this->courseURL = $courseURL;
        $this->masterRubric = $masterRubric;
    }
    
    function readStudent($studentID) : Student{
        // $data = $this->fetchStudentVakbeheersing($studentID);
        // echo "<pre>";
        // echo json_encode($data, JSON_PRETTY_PRINT); // rubric details
        // echo "</pre>";
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
        // var_dump($data);
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

    private function fetchMasterRubric(){
        return cached_call(
            '_fetchMasterRubricInternalUncached',
            [$this->courseURL, $this->apiKey, $this->masterRubric],
            86400 //Cache for 1 day
        );
    }

    private function fetchStudentResults($studentID){
        $url = "$this->courseURL/students/submissions?student_ids[]=$studentID&include[]=rubric_assessment";
        $data = curlCall($url, $this->apiKey, 300); //Cache for 5 minutes
        return $data;
    }

    public function fetchStudentVakbeheersing($studentID){
        $url = "$this->courseURL/outcome_results";//?user_ids[]=$studentID";
        $data = curlCall($url, $this->apiKey, 300); //Cache for 5 minutes
        return $data;
    }

    private function fetchStudentDetails($studentID){
        $url = "$this->courseURL/users/$studentID";
        $data = curlCall($url, $this->apiKey, 60*60*24); //Cache for 1 day
        return $data;
    }
}