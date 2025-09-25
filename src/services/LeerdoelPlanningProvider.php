<?php

class LeerdoelPlanningProvider{
    private $id_to_leerdoel = [];

    public function getLeerdoelById($id){
        return $this->id_to_leerdoel[$id];
    }

    public static function getPlanning(CanvasReader $canvasreader) : LeerdoelPlanning {
        $loaded = self::loadFromFile();
        $canvasdata = self::getLeerdoelenFromCanvas($canvasreader);
        $merged = self::merge($canvasdata, $loaded);
        $merged->debugPopulateMissingToetsmomenten();
        return $merged;
    }

    private static function getLeerdoelenFromCanvas(CanvasReader $canvasreader) : LeerdoelPlanning {
        $canvasdata = $canvasreader->fetchMasterRubric()["data"];
        $newPlanning = new LeerdoelPlanning();

        foreach($canvasdata as $item){
            $name = $item["description"];
            $canvasID = $item["id"];
            $canvasLeeruitkomstID = $item["learning_outcome_id"];
            $mastery_points = $item["mastery_points"];
            $newLeerdoel = new Leerdoel();
            $newLeerdoel->naam = $name;
            $newLeerdoel->id_in_canvas = $canvasID;
            $newLeerdoel->meesterschapsNiveau = $mastery_points;
            $newLeerdoel->leeruitkomstIDInCanvas = $canvasLeeruitkomstID;
            
            foreach($item["ratings"] as $rating){
                $niveau = $rating["points"];
                $newLeerdoel->addBeschrijving($niveau, $rating["long_description"]);
            }

            $newPlanning->addLeerdoel($newLeerdoel);
        }

        return $newPlanning;
    }

    private static function merge($A, LeerdoelPlanning $B){
        $newPlanning = new LeerdoelPlanning();
        // Match up leerdoelen by name and merge them using the mergeLeerdoelen static function

        //map them each to an array using their name as keys.        
        //get array of all unique keys.
        $allNames = array_unique(array_merge(
            array_map(fn($ld) => $ld->naam, array_merge(...array_values($A->getAll()))),
            array_map(fn($ld) => $ld->naam, array_merge(...array_values($B->getAll())))
        ));

        //Loop over them and merge if exists in both, otherwise add to the new planning.
        foreach($allNames as $name){
            $leerdoelA = $A->getLeerdoelByName($name);
            $leerdoelB = $B->getLeerdoelByName($name);
            if($leerdoelA != null && $leerdoelB != null){
                //Both exist, merge them
                $merged = self::mergeLeerdoelen($leerdoelA, $leerdoelB);
                $newPlanning->addLeerdoel($merged);
            } elseif ($leerdoelA != null) {
                //Only in A
                $newPlanning->addLeerdoel($leerdoelA);
            } else {
                //Only in B
                $newPlanning->addLeerdoel($leerdoelB);
            }
        }
        return $newPlanning;
    }

    private static function mergeLeerdoelen(Leerdoel $a, Leerdoel $b) : Leerdoel {
        if($a->naam != $b->naam){
            throw new Exception("Cannot merge different leerdoelen: '".$a->naam."' and '".$b->naam."'");
        }

        $categorie = ($a->categorie != "") ? $a->categorie : $b->categorie;

        //Merge descriptions by taking the longest one
        $beschrijvingen = [];
        while(count($a->beschrijvingen) > 0 || count($b->beschrijvingen) > 0){
            $beschrijving = "";
            if(count($a->beschrijvingen) > 0){
                $beschrijving = array_shift($a->beschrijvingen);
            }
            if(count($b->beschrijvingen) > 0){
                $beschrijvingB = array_shift($b->beschrijvingen);
                if(strlen($beschrijvingB) > strlen($beschrijving)){
                    $beschrijving = $beschrijvingB;
                }
            }
            array_push($beschrijvingen, $beschrijving);
        }

        $toetsmomentenTotal = [];
        while(count($a->toetsmomenten) > 0 || count($b->toetsmomenten) > 0){
            $namesa = (count($a->toetsmomenten) > 0) ? array_shift($a->toetsmomenten) : [];
            $namesb = (count($b->toetsmomenten) > 0) ? array_shift($b->toetsmomenten) : [];
            $toetsmomenten = array_unique(array_merge($namesa, $namesb));
            sort($toetsmomenten);
            array_push($toetsmomentenTotal, $toetsmomenten);
        }

        //Choose the optelModel that is not Null
        $optelModel = ($a->optelModel != optelModel::Null && $a->optelModel != null) ? $a->optelModel : $b->optelModel;

        $id_in_canvas = ($a->id_in_canvas != null) ? $a->id_in_canvas : $b->id_in_canvas;

        $newLeerdoel = new Leerdoel();
        $newLeerdoel->naam = $a->naam;
        $newLeerdoel->categorie = $categorie;
        $newLeerdoel->beschrijvingen = $beschrijvingen;
        $newLeerdoel->toetsmomenten = $toetsmomentenTotal;
        $newLeerdoel->optelModel = $optelModel;
        $newLeerdoel->id_in_canvas = $id_in_canvas;
        $newLeerdoel->meesterschapsNiveau = max($a->meesterschapsNiveau, $b->meesterschapsNiveau);

        return $newLeerdoel;
    }

    private static function loadFromFile($filename = __DIR__ . '/../data/leerdoelen.json') : LeerdoelPlanning {

        if (!file_exists($filename)) {
            throw new Exception("File not found: " . $filename);
        }

        $json = file_get_contents($filename);
        $data = json_decode($json, true);

        if ($data === null) {
            throw new Exception("Invalid JSON in file: " . $filename);
        }

        $newone = new LeerdoelPlanning();

        foreach ($data['leerdoelen'] as $leerdoelData) {
            $leerdoel = new Leerdoel();
            $leerdoel->categorie = $leerdoelData['categorie'] ?? "";
            $leerdoel->naam = $leerdoelData['naam'] ?? "";
            $leerdoel->beschrijvingen = $leerdoelData['beschrijvingen'] ?? [];
            if(isset($leerdoelData['optelModel'])){
                echo "optelmodel: " . $leerdoelData["optelModel"] . "<br/>";
            }
            $leerdoel->optelModel = optelModel::from($leerdoelData['optelModel'] ?? "Null");
            echo "optelmodel gerealiseerd: " . $leerdoel->optelModel->value . "<br/>";
            $leerdoel->toetsmomenten = $leerdoelData['toetsmomenten'] ?? [];
            $newone->addLeerdoel($leerdoel);
        }

        return $newone;
    }
}