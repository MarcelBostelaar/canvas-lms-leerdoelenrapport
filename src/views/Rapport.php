<?php

$niveauClasses = ["NietBehaald", "Beginner", "Gevorderde", "Eindexamenniveau"];

function renderRapport($student, $leerdoelResultaat, $leerdoelPlanning, $aantalPeriodes = 12, $currentPeriode = 6) {
    global $niveauClasses;
    echo "hello world";
    echo "<br>";
    var_dump($student);
    echo "<br>";
    var_dump($leerdoelResultaat);
    echo "<br>";
    var_dump($leerdoelPlanning);

    ?>
    <style>
        .NietBehaald { background-color: #a0a0a0ff; }
        .Beginner { background-color: #d2c92fff; }
        .Gevorderde { background-color: #27acc4ff; }
        .Eindexamenniveau { background-color: #3812c3ff; }
        .behaald { border: 2px solid #155724; }
        .current { border: 2px solid #e81010ff; }
    </style>
    <?php

    echo "<h2>Student: " . htmlspecialchars($student->naam) . "</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Leerdoel</th>";
    for ($p = 1; $p <= $aantalPeriodes; $p++) {
        echo "<th>Periode $p</th>";
    }
    echo "</tr>";

    foreach ($leerdoelPlanning as $leerdoel => $niveaus) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($leerdoel) . "</td>";
        $behaaldNiveau = $leerdoelResultaat->getBehaaldNiveau($leerdoel)->value;
        $currentNiveau = 0;
        $behaald = true;
        for ($p = 1; $p <= $aantalPeriodes; $p++) {
            if ($niveaus['b'] === $p) {
                $currentNiveau = 1;
            }
            elseif ($niveaus['g'] === $p) {
                $currentNiveau = 2;
            }
            if ($niveaus['e'] === $p) {
                $currentNiveau = 3;
            }
            if ($behaaldNiveau < $currentNiveau) {
                $behaald = false;
            }
            echo "<td class='" . $niveauClasses[$currentNiveau] . 
                ($behaald && ($behaaldNiveau > 0) ? " behaald" : "") . 
                ($p ==  $currentPeriode? " current" : "") . "'>";
        }
        echo "</tr>";
    }
    echo "</table>";
}