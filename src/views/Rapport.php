<?php


function filterOnlyLetters($string){
    return preg_replace("/[^a-zA-Z0-9]/", "", $string);
}

function renderRapport($student, $leerdoelPlanning, $aantalPeriodes = 12) {
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
    <?php

    var_dump($student->resultaten);

    echo "<script type='text/javascript'>\n";
    echo "let resultaten = {};\n";
    $index = 0;
    foreach($student->resultaten as $resultaat){
        ?>
        resultaten["<?php echo $resultaat->beschrijving;?>"] = {
            <?php
            foreach($resultaat->getAll() as $naam => $content){ ?>
            "<?php echo $naam;?>" : {
                "niveau" : <?php echo $content["niveau"]->value?>,
                "periode" : <?php echo $content["periode"]?>
            },
            <?php } ?>
        };
        <?php
    }
    echo "</script>";
    echo "<script src='./../static/singlestudentview.js' type='text/javascript'></script>";
    echo '<link rel="stylesheet" href="./../static/style.css">';


    echo "<h2>Student: " . htmlspecialchars($student->naam) . "</h2>";
    echo "<h3>Resultaten</h3>";
    echo "<form id='resultaten_form'></form>";

    echo "<table>";
    echo "<tr><th>Leerdoel</th>";

    echo "<th></th>";
    for ($p = 1; $p <= $aantalPeriodes; $p++) {
        echo "<th>Periode $p</th>";
    }
    echo "</tr>";

    foreach ($leerdoelPlanning->getAll() as $leerdoel) {
        $leerdoelAsClass = 'leerdoel_' . filterOnlyLetters($leerdoel->naam);
        echo "<tr id='$leerdoelAsClass'>";
        echo "<td>" . htmlspecialchars($leerdoel->naam) . "</td>";
        echo "<td class='toetsniveau_0 periode_0 rapportcell first last'></td>";
        for ($p = 1; $p <= $aantalPeriodes; $p++) {
            $toetsniveau = $leerdoel->getToetsNiveauInPeriode($p);
            echo "<td class='toetsniveau_$toetsniveau periode_$p rapportcell " .
            ($leerdoel->getFirstToetsNiveauPeriode($toetsniveau) == $p ? "first " : "") .
            ($leerdoel->getLastToetsNiveauPeriode($toetsniveau) == $p ? "last " : "") .
            "'>";
        }
        echo "</tr>";
    }
    echo "</table>";
}