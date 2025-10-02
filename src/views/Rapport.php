<?php


function filterOnlyLetters($string){
    return preg_replace("/[^a-zA-Z0-9]/", "", $string);
}

function renderLeerdoelCategorie(Student $student, LeerdoelenStructuur $leerdoelenStructuur, $aantalPeriodes){

    echo "<tr><th colspan='" . ($aantalPeriodes + 2) . "'>" . htmlspecialchars($leerdoelenStructuur->categorie) . "</th></tr>";
    $nonChildLeerdoelen = $leerdoelenStructuur->getNonChildLeerdoelen();
    if(count($nonChildLeerdoelen) > 0){
        echo "<tr><th>Leerdoel</th>";

        echo "<th></th>";
        for ($p = 1; $p <= $aantalPeriodes; $p++) {
            echo "<th>Periode $p</th>";
        }
        echo "</tr>";

        foreach ($nonChildLeerdoelen as $leerdoel) {
            $leerdoelAsClass = 'leerdoel_' . filterOnlyLetters($leerdoel->naam);
            echo "<tr id='$leerdoelAsClass'>";
            echo "<td>" . htmlspecialchars($leerdoel->naam) . "</td>";
            echo "<td class='toetsniveau_0 periode_0 rapportcell first last'></td>";
            for ($p = 1; $p <= $aantalPeriodes; $p++) {
                $toetsniveau = $leerdoel->getExactToetsNiveauInPeriode($p);
                echo "<td class='toetsniveau_$toetsniveau periode_$p rapportcell " .
                ($leerdoel->getFirstToetsNiveauPeriode($toetsniveau) == $p ? "first " : "") .
                ($leerdoel->getLastToetsNiveauPeriode($toetsniveau) == $p ? "last " : "") .
                "'>";
            }
            echo "</tr>";
        }
    }
    foreach($leerdoelenStructuur->getChildren() as $childGroup){
        renderLeerdoelCategorie($student, $childGroup, $aantalPeriodes);
    }
}

function LeerresultatenToJS($resultaten, $addTo){
    foreach($resultaten as $resultaat){
        ?>
        <?php echo $addTo . '["' . $resultaat->beschrijving;?>"] = {
            <?php
            // var_dump($resultaat);
            foreach($resultaat->getAll() as $naam => $content){ ?>
            "<?php echo $naam;?>" : {
                "niveau" : <?php echo $content["niveau"]?>,
                "periode" : <?php echo '"' . $content["datum"]->format('Y-m-d') . '"'?>
            },
            <?php } ?>
        };
        <?php
    }
}

function renderRapport(Student $student, LeerdoelenStructuur $leerdoelenStructuur, array $uitkomsten, $aantalPeriodes = 12) {
    
    echo "<script type='text/javascript'>\n";
    // echo "<pre>";
    echo "let resultaten = {};\n";
    LeerresultatenToJS($uitkomsten, 'resultaten');
    echo "</script>";
    // echo "</pre>";
    echo "<script src='/static/singlestudentview.js' type='text/javascript'></script>";
    echo '<link rel="stylesheet" href="/static/singlestudentview.css">';


    echo "<h2>Student: " . htmlspecialchars($student->name) . "</h2>";
    echo '<button onclick="refresh()">Refresh</button>';
    echo "<h3>Resultaten</h3>";
    echo "<form id='resultaten_form'></form>";

    echo "<table>";
    renderLeerdoelCategorie($student, $leerdoelenStructuur, $aantalPeriodes);
    echo "</table>";
}