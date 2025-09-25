<?php

class CanvasLeerdoelProvider{
    private $canvasReader;

    public function __construct(CanvasReader $canvasReader){
        $this->canvasReader = $canvasReader;
    }

    public function getTotal(){
        $groupMap = [];
        $outcomeGroups = $this->canvasReader->fetchAllOutcomeGroups();

        foreach($outcomeGroups as $outcomeGroup){
            $id = $outcomeGroup["id"];
            $groupMap[$id] = [
                "title" => $outcomeGroup["title"]
            ];
            if(isset($outcomeGroup["parent_outcome_group"])){
                $groupMap[$id]["parentID"] = $outcomeGroup["parent_outcome_group"]["id"];
            }
            else{
                $groupMap[$id]["parentID"] = null;
            }
        }

        foreach($groupMap as $id => $group){
            $groupMap[$id]["uitkomst"] = $this->getLeeruitkomsten($id);
        }
        $topLevel = null;
        foreach($groupMap as $id => $group){
            if($group["parentID"] != null){
                $parent = $groupMap[$group["parentID"]]["uitkomst"];
                $parent->addChildGroup($group["uitkomst"]);
            }
            else{
                $topLevel = $group["outcome"];
            }
        }
        return $topLevel;
    }

    private function getLeeruitkomsten($id): LeerdoelPlanning{
        $leeruitkomstData = $this->canvasReader->fetchOutcomesOfGroup($id);
        $planning = new LeerdoelPlanning();
        $planning->categorie = $leeruitkomstData["title"];
        foreach($leeruitkomstData as $leeruitkomst){
            $id = $leeruitkomst["id"];
            $title = $leeruitkomst["title"];
            $points_possible = $leeruitkomst["points_possible"];
            $mastery_points = $leeruitkomst["mastery_points"];
            $calcMethod = $leeruitkomst["calculation_method"];
            $runningAverageValue = null;
            if($calcMethod == "highest"){
                $calcMethod = optelModel::Hoogste;
            }
            else if(is_numeric($calcMethod)){
                $runningAverageValue = $calcMethod;
                $calcMethod = optelModel::RunningAverage;
            }
            else if($calcMethod == "average"){
                $calcMethod = optelModel::Gemiddelde;
            }
            else{
                throw new Exception("Unknown calc method");
            }
            $ratings = $leeruitkomst["ratings"];
            //sort to get ratings from 0 to higher
            usort($ratings, function ($a, $b) {
                return $a['points'] <=> $b['points'];
            });

            $leerdoel = new Leerdoel();
            $leerdoel->naam = $title;
            $leerdoel->leeruitkomstIDInCanvas = $id;
            $leerdoel->optelModel = $calcMethod;
            $leerdoel->runningAverageValue = $runningAverageValue;
            $leerdoel->meesterschapsNiveau = $mastery_points;
            for( $i = 0; $i < max(count($ratings), ($points_possible + 1)); $i++ ){
                $leerdoel->addBeschrijving($i, $ratings[$i]["description"]);
            }
            $planning->addLeerdoel($leerdoel);
        }
        return $planning;
    }
    /*TODO:
        Fetch all groups from canvas
        For each group, fetch all outcomes
        For each outcome, fetch all associated mastery levels and descriptions and calculation methods
        Build hierarchy of structure via leerdoelplanning objects
    */
}