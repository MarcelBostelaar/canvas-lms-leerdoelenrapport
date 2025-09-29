<?php
require_once __DIR__ . '/../util/UtilFuncs.php';
require_once __DIR__ . '/../util/Caching/Caching.php';
require_once __DIR__ . '/../util/Constants.php';
require_once __DIR__ . "/../util/Caching/ICacheSerialisable.php";
require_once __DIR__ . "/../util/Caching/CourseRestricted.php";

class CanvasLeerdoelProvider extends ICacheSerialisable{
    private $canvasReader;

    public function __construct(CanvasReader $canvasReader){
        $this->canvasReader = $canvasReader;
    }

    public function serialize(ICacheSerialiserVisitor $visitor): string {
        return "CanvasLeerdoelProvider - " . $visitor->serializeCanvasReader($this->canvasReader);
    }

    public function getTotal(): LeerdoelenStructuur{
        global $sharedCacheTimeout;
        return cached_call(
            [$this, '_getTotal'],
            [],
            $sharedCacheTimeout, //cached globally
            [],
            new CourseRestricted()//TODO change to Cache
        );
    }
    public function _getTotal(): LeerdoelenStructuur{
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
            $groupMap[$id]["uitkomst"] = $this->getLeeruitkomsten($id, $group["title"]);
        }
        $topLevel = null;
        foreach($groupMap as $id => $group){
            if($group["parentID"] != null){
                $parent = $groupMap[$group["parentID"]]["uitkomst"];
                $parent->addChildGroup($group["uitkomst"]);
            }
            else{
                $topLevel = $group["uitkomst"];
            }
        }
        return $topLevel;
    }

    private function getLeeruitkomsten($groupID, $groupName): LeerdoelenStructuur{
        $leeruitkomstData = $this->canvasReader->fetchOutcomesOfGroup($groupID);
        // echo "<pre>";
        // var_dump($leeruitkomstData);
        // echo "</pre>";
        $planning = new LeerdoelenStructuur();
        $planning->categorie = $groupName;
        foreach($leeruitkomstData as $leeruitkomst){
            $id = $leeruitkomst["id"];
            $titel = $leeruitkomst["title"];
            // echo "id leeruitkomst: $id - $titel<br>";
            
            // echo "<pre>";
            // var_dump($leeruitkomst);
            // echo "</pre>";
            $leeruitkomst = $this->canvasReader->fetchOutcome($id);
            //check if all fields are present
            $result = isSetMany($leeruitkomst, "id", "title", "points_possible", "mastery_points", "calculation_method", "ratings");
            if($result[0] === false){
                // $title = isset($leeruitkomst['title']) ? $leeruitkomst['title'] : 'unknown';
                // echo "Missing fields in leeruitkomst '$title': $id<br>";
                // var_dump($leeruitkomst);
                // echo "<pre>";
                // var_dump($leeruitkomst);
                // echo "</pre>";
                continue;
            }
            //single outcome
            $id = $leeruitkomst["id"];
            $title = $leeruitkomst["title"];
            $points_possible = $leeruitkomst["points_possible"];
            $mastery_points = $leeruitkomst["mastery_points"];
            $calcMethod = $leeruitkomst["calculation_method"];
            $runningAverageValue = null;
            if($calcMethod == "highest"){
                $calcMethod = optelModel::Hoogste;
            }
            else if($calcMethod == "standard_decaying_average"){
                $runningAverageValue = $leeruitkomst["calculation_int"];
                $calcMethod = optelModel::RunningAverage;
            }
            else if($calcMethod == "average"){
                $calcMethod = optelModel::Gemiddelde;
            }
            else{
                // echo "<pre>";
                // var_dump($leeruitkomst);
                // echo "</pre>";
                throw new Exception("Unknown calc method: '$calcMethod' for leeruitkomst '$title'");
            }
            $ratings = $leeruitkomst["ratings"];
            //sort to get ratings from 0 to higher
            usort($ratings, function ($a, $b) {
                return $a['points'] <=> $b['points'];
            });
            // echo "<pre>";
            // var_dump($leeruitkomst);
            // echo "</pre>";
            // throw new Exception("Stop");
            $leerdoel = new Leerdoel();
            $leerdoel->naam = $title;
            // $leerdoel->id_in_canvas = $groupID;//klopt niet
            $leerdoel->leeruitkomstIDInCanvas = $id;//klopt niet
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
}