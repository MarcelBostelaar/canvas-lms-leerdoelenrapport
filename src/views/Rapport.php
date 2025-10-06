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

function LeerresultatenToDict($resultaten){
    $dictform = array_map_assoc(fn($_, $r) => 
    [
        "key" => $r->beschrijving,
        "value" => array_map_assoc(fn($k, $v) => 
            [
                "key" => $k,
                "value" => [
                    "niveau" => $v->niveau,
                    "datum" => $v->datum->format('Y-m-d')
                ]
            ]
            ,$r->getAll())
    ]
    , $resultaten);

    // $dictform = json_encode($dictform);
    return $dictform;
}

/**
 * Summary of renderRapport
 * @param Student $student
 * @param LeerdoelenStructuur $leerdoelenStructuur
 * @param LeerdoelResultaat[] $uitkomsten
 * @param mixed $aantalPeriodes
 * @return void
 */
function renderRapport(Student $student, LeerdoelenStructuur $leerdoelenStructuur, array $uitkomsten, $aantalPeriodes = 12) {
    $jsdict = array_map_assoc(fn($k, $v) => [
        "key" => $k,
        "value" => LeerresultatenToDict($v)
    ], $uitkomsten);
    $jsdict = json_encode($jsdict);
    echo "<script type='text/javascript'>\n";
    // echo "<pre>";
    echo "let resultaten = $jsdict;\n";
    echo "</script>";
    // echo "</pre>";
    echo "<script src='/static/singlestudentview.js' type='text/javascript'></script>";
    echo '<link rel="stylesheet" href="/static/singlestudentview.css">';

    ?>

    <div class="html_template marker_group">
        <div class="title_container">
            <input type="checkbox" checked class="supercheck">
            <h3 class="title">Your title here</h3>
        </div>
        <div class="marker_container">

        </div>
    </div>


    <h2>Student: <?=htmlspecialchars($student->name)?></h2>
    <button onclick="refresh()">Refresh</button>
    <div id="resultcontent">
        <div id="resultaten">
            <div class="followsticky">
                <h3>Resultaten</h3>
                <form id='resultaten_form'></form>
            </div>
        </div>

        <table>
        <?php renderLeerdoelCategorie($student, $leerdoelenStructuur, $aantalPeriodes);?>
        </table>
    </div>

    <?php
}