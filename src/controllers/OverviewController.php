<?php
require_once __DIR__ . '/../services/CanvasReader.php';
require_once __DIR__ . '/../services/GroupingProvider.php';

class OverviewController{
    public function index(){
        $canvasReader = CanvasReader::getReader();
        $canvasReader = new CanvasReader($canvasReader->getApiKey(), $canvasReader->getBaseURL(), 70126);
        $groupings = (new GroupingProvider($canvasReader))->getSectionGroupings();

        echo "<h1>Overview of students</h1>";
        foreach($groupings->getAllGroupings() as $groupName => $grouping){
            $currentPeriod = $grouping->getPeriodOnDate(new DateTime())->period;
            $currentPeriod = $currentPeriod ? $currentPeriod : "No current period";        
            echo "<h2>Group: $groupName</h2>";
            echo "<p>Current period: $currentPeriod</p>";
            foreach($grouping->sections as $section){
                echo "<h3>Section: " . $section->name . "</h3>";
                echo "<ul>";
                foreach($section->getStudents($canvasReader) as $student){
                    $studentsectionnametest = $student->activeSection->name;
                    echo "<li><a href='./SingleStudentViewController.php?studentID=$student->id'>" . htmlspecialchars($student->name) . " - $student->id - $studentsectionnametest</a></li>";
                }
                echo "</ul>";
            }
        }
    }
}

$x = new OverviewController();
$x->index();