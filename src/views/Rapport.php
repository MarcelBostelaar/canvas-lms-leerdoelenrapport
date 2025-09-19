<?php


function filterOnlyLetters($string){
    return preg_replace("/[^a-zA-Z0-9]/", "", $string);
}

function renderRapport($student, $leerdoelResultaat, $leerdoelPlanning, $aantalPeriodes = 12, $currentPeriode = 6) {
    echo "hello world";
    ?>
    
    <style>
        td{
            border: 1px solid black;
        }

        .toetsniveau_0 {
            background-color: grey;
        }
        .toetsniveau_1 {
            background-color: lightblue;
        }
        .toetsniveau_2 {
            background-color: lightgreen;
        }
        .toetsniveau_3 {
            background-color: orange;
        }
    </style>

    <style>
        .leerdoel_
    </style>

    
    <?php


    echo "<h2>Student: " . htmlspecialchars($student->naam) . "</h2>";
    echo "<table>";
    echo "<tr><th>Leerdoel</th>";
    for ($p = 1; $p <= $aantalPeriodes; $p++) {
        echo "<th>Periode $p</th>";
    }
    echo "</tr>";

    foreach ($leerdoelPlanning->getAll() as $leerdoel) {
        $leerdoelAsClass = 'leerdoel_' . filterOnlyLetters($leerdoel->naam);
        echo "<tr class='$leerdoelAsClass'>";
        echo "<td>" . htmlspecialchars($leerdoel->naam) . "</td>";
        for ($p = 1; $p <= $aantalPeriodes; $p++) {
            $toetsniveau = $leerdoel->getToetsNiveauInPeriode($p);
            echo "<td class='toetsniveau_$toetsniveau periode_$p " .
            ($leerdoel->getFirstToetsNiveauPeriode($toetsniveau) == $p ? "first " : "") .
            ($leerdoel->getLastToetsNiveauPeriode($toetsniveau) == $p ? "last " : "") .
            "'>";
        }
        echo "</tr>";
    }
    echo "</table>";
}