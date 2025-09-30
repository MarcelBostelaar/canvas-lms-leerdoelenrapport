<?php

function RenderOverview(AllSectionGroupings $groupings){
    echo "<script src='/static/apitools.js'></script>";
    echo "<script src='/static/overview.js'></script>";
    echo "<link rel='stylesheet' href='/static/overview.css'>";
    echo "<h1>Overview of students</h1>";
    foreach($groupings->getAllGroupings() as $groupName => $grouping){
        $currentPeriod = $grouping->getPeriodOnDate(new DateTime())->period;
        $currentPeriod = $currentPeriod ? $currentPeriod : "No current period";        
        echo "<h2>Group: $groupName</h2>";
        echo "<p>Current period: $currentPeriod</p>";
        foreach($grouping->sections as $section){
            echo "<h3>Section: " . $section->name . "</h3>";
            echo "<ul>";
            foreach($section->getStudents() as $student){
                $studentsectionnametest = $student->activeSection->name;
                echo "<li><a href='./SingleStudentViewController.php?studentID=$student->id'>" 
                . htmlspecialchars($student->name) . "
                <div id='progress_box_$student->id'
                class='progress_box'
                target_period='$currentPeriod'></div></a></li>";
            }
            echo "</ul>";
        }
    }
}