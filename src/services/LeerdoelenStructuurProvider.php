<?php
require_once __DIR__ . '/../models/LeerdoelenStructuur.php';
require_once __DIR__ . '/CanvasLeerdoelProvider.php';

class LeerdoelenStructuurProvider{

    public static function getStructuur(CanvasReader $canvasreader) : LeerdoelenStructuur {
        $loaded = self::loadFromFile();
        $canvasdata = (new CanvasLeerdoelProvider($canvasreader))->getTotal();
        // echo "<pre>";
        // var_dump($canvasdata);
        // echo "</pre>";
        self::merge($canvasdata, $loaded);
        
        $canvasdata->debugPopulateMissingToetsmomenten();
        return $canvasdata;
    }

    /**
     * Merges a list of Leerdoelen into an existing LeerdoelenStructuur
     * @param LeerdoelenStructuur $A
     * @param Leerdoel[] $B
     * @throws \Exception
     * @return never
     */
    private static function merge(LeerdoelenStructuur $A, array $B): void{
        foreach($B as $leerdoel){
            $other = $A->getLeerdoelByName($leerdoel->naam);
            if($other == null){
                throw new Exception("Cannot find leerdoel '".$leerdoel->naam."' in exisitng data");
            }
            $merged = self::mergeLeerdoelen($leerdoel, $other);
            $A->updateLeerdoel($merged);
        }
        return;
    }

    private static function mergeLeerdoelen(Leerdoel $a, Leerdoel $b) : Leerdoel {
        if($a->naam != $b->naam){
            throw new Exception("Cannot merge different leerdoelen: '".$a->naam."' and '".$b->naam."'");
        }

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

        $id_in_canvas = ($a->leeruitkomstIDInCanvas != null) ? $a->leeruitkomstIDInCanvas : $b->leeruitkomstIDInCanvas;

        $newLeerdoel = new Leerdoel();
        $newLeerdoel->naam = $a->naam;
        $newLeerdoel->beschrijvingen = $beschrijvingen;
        $newLeerdoel->toetsmomenten = $toetsmomentenTotal;
        $newLeerdoel->optelModel = $optelModel;
        $newLeerdoel->id_in_canvas = $id_in_canvas;
        $newLeerdoel->meesterschapsNiveau = max($a->meesterschapsNiveau, $b->meesterschapsNiveau);

        return $newLeerdoel;
    }

    /**
     * Summary of loadFromFile
     * @param mixed $filename
     * @throws \Exception
     * @return Leerdoel[]
     */
    private static function loadFromFile($filename = __DIR__ . '/../data/leerdoelen.json') : array {

        if (!file_exists($filename)) {
            throw new Exception("File not found: " . $filename);
        }

        $json = file_get_contents($filename);
        $data = json_decode($json, true);

        if ($data === null) {
            throw new Exception("Invalid JSON in file: " . $filename);
        }

        $newone = [];

        foreach ($data as $leerdoelData) {
            $leerdoel = new Leerdoel();
            $leerdoel->naam = $leerdoelData['naam'] ?? "";
            $leerdoel->beschrijvingen = $leerdoelData['beschrijvingen'] ?? [];
            $leerdoel->toetsmomenten = $leerdoelData['toetsmomenten'] ?? [];
            array_push($newone, $leerdoel);
        }

        return $newone;
    }
}