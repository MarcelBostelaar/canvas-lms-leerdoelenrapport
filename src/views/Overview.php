<?php

function RenderOverview(AllSectionGroupings $groupings, DateTime $showForDate){
    $dateformatted = $showForDate->format("Y-m-d");
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <script src="/static/apitools.js"></script>
    <script src="/static/overview.js"></script>
    <link rel="stylesheet" href="/static/overview.css">
    <button onclick="refresh()">Refresh</button><br>
    Laat zien voor datum: <input type="date" id="date" value="<?= htmlspecialchars($dateformatted) ?>"><br>
    <button onclick="downloadAllPdfFiles()">Download rapporten</button>
    <h1>Overview of students</h1>
    <?php
    foreach($groupings->getAllGroupings() as $groupName => $grouping){
        $currentPeriod = $grouping->getPeriodOnDate($showForDate)->period;
        $currentPeriod = $currentPeriod ? $currentPeriod : "No current period";        
        echo "<h2>Group: $groupName</h2>";
        echo "<p>Current period: $currentPeriod</p>";
        foreach($grouping->sections as $section){
            echo "<h3>Section: " . $section->name . "</h3>";
            echo "<ul>";
            foreach($section->getStudents() as $student){
                $studentsectionnametest = $student->activeSection->name;
                echo "<li><a href='./SingleStudentViewController.php?id=$student->id&date=$dateformatted'>" 
                . htmlspecialchars($student->name) . "
                <div id='progress_box_$student->id'
                class='progress_box'
                target_period='$currentPeriod'></div></a></li>";
            }
            echo "</ul>";
        }
    }
}