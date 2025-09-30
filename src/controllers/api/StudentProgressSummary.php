<?php

require_once __DIR__ . '/../../services/CanvasReader.php';
require_once __DIR__ . '/../../services/StudentProvider.php';
require_once __DIR__ . '/../../services/LeerdoelenStructuurProvider.php';
require_once __DIR__ . '/../../models/Leerdoel.php';

class StudentProgressSummary {
    public function index(){
        try{
            $canvasReader = CanvasReader::getReader();
            $studentID = $_GET['studentID'];
            $targetPeriod = $_GET['currentPeriod'];

            $student = (new StudentProvider($canvasReader))->getByID($studentID);
            $mastery = $student->getMasteryResults($canvasReader);
            $planning = (new LeerdoelenStructuurProvider($canvasReader))->getStructuur();
            $results = array_map(
                fn(Leerdoel $leerdoel) => 
                        [($mastery->getBehaaldNiveau($leerdoel) ?? 0), $leerdoel->getMostRecentToetsNiveauInPeriode($targetPeriod)]
                , $planning->getAllLeerdoelen());

            //testdata, generate 52 random numbers between 0 and 120
            $randomLevel = rand(3, 8);
            $results = array_map(fn($x) => [rand($randomLevel, 8) / 2, 3], range(1, 52));
            if(rand(0, 8) == 0){
                //One out of 8 cases, perfect scores
                $results = array_map(fn($x) => [rand(6, 8) / 2, 3], range(1, 52));
            }


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

            header('Content-Type: application/json');
            echo json_encode([
                "total" => $totalResults,
                "on_track" => $on_track,
                "exceeded" => $exceeded,
                "behind" => $behind,
                "points_behind" => $pointsBehind,
                "total_points_needed" => $totalPointsNeeded
            ]);
        }catch(Exception $e){
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}

$x = new StudentProgressSummary();
$x->index();