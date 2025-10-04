<?php

require_once __DIR__ . '/../../services/StudentProvider.php';
require_once __DIR__ . '/../../services/LeerdoelenStructuurProvider.php';
require_once __DIR__ . '/../../models/Leerdoel.php';
require_once __DIR__ . '/APIController.php';

class StudentProgressSummary extends APIController {
    protected $debug_keep_output = true;
    public function handle(){
        global $providers;
        $studentID = (int)$_GET['id'];
        $targetPeriod = (int)$_GET['currentPeriod'];

        $student = $providers->studentProvider->getByID($studentID);
        $mastery = $student->getMasteryResults();
        $planning = $providers->leerdoelenStructuurProvider->getStructuur();
        $results = array_map(
            fn(Leerdoel $leerdoel) => 
                    [($mastery->getBehaaldNiveau($leerdoel) ?? 0), $leerdoel->getMostRecentToetsNiveauInPeriode($targetPeriod)]
            , $planning->getAllLeerdoelen());

        //Count how many are on track, exceeded or behind
        
        $totalResults = count($results);
        //Amount of items that are exactly on track
        $on_track = count(array_filter($results, fn($r) => $r[0] == $r[1]));
        //Amount of items that are exceeded
        $exceeded = count(array_filter($results, fn($r) => $r[0] > $r[1]));
        //Amount of items that are behind
        $behind = count(array_filter($results, fn($r) => $r[0] < $r[1]));

        $pointsBehind = round(array_sum(array_map(fn($r) => max($r[1] - $r[0], 0), $results)));
        $totalPointsNeeded = array_sum(array_map(fn($r) => $r[1], $results));

        return [
            "total" => $totalResults,
            "on_track" => $on_track,
            "exceeded" => $exceeded,
            "behind" => $behind,
            "points_behind" => $pointsBehind,
            "total_points_needed" => $totalPointsNeeded
        ];
    }
}

$x = new StudentProgressSummary();
$x->index();