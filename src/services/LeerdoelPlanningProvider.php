<?php

class LeerdoelPlanningProvider{
    private $id_to_leerdoel = [];

    public function getLeerdoelById($id){
        return $this->id_to_leerdoel[$id];
    }

    public static function getPlanning(CanvasReader $canvasreader) : LeerdoelPlanning {
        $loaded = self::loadFromFile();
        $canvasdata = $canvasreader->fetchStrippedDownMasterRubric();
        self::addIdsFromCanvasData($canvasdata, $loaded);
        return $loaded;
    }

    private static function addIdsFromCanvasData($canvasData, LeerdoelPlanning $leerdoelPlanning){
        foreach($leerdoelPlanning->getAll() as $categorie => $leerdoelen){
            foreach($leerdoelen as $leerdoel){
                if(!isset($canvasData[$leerdoel->naam])){
                    $alleLeerdoelenInCanvas = array_keys($canvasData);
                    echo "Beschikbare leerdoelen in Canvas: <br>".implode(",<br> ", $alleLeerdoelenInCanvas)."\n";
                    throw new Exception("Leerdoel naam '".$leerdoel->naam."' niet gevonden in Canvas data");
                }
                $leerdoel->id_in_canvas = $canvasData[$leerdoel->naam]['learning_outcome_id'];
                $leerdoelPlanning->id_to_leerdoel[$leerdoel->id_in_canvas] = $leerdoel;
            }
        }
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
            $leerdoel = new Leerdoel(
                $leerdoelData['categorie'],
                $leerdoelData['naam'],
                $leerdoelData['beschrijvingBeginner'],
                $leerdoelData['beschrijvingGevorderde'],
                $leerdoelData['beschrijvingEindexamenniveau'],
                $leerdoelData['beschrijvingBovenEindexamenniveau'],
                constant('optelModel::' . $leerdoelData['optelModel'])
            );

            if (!empty($leerdoelData['toetsmomentenBeginner'])) {
                foreach ($leerdoelData['toetsmomentenBeginner'] as $toetsmoment) {
                    $leerdoel->addToetsmomentBeginner($toetsmoment);
                }
            }
            if (!empty($leerdoelData['toetsmomentenGevorderde'])) {
                foreach ($leerdoelData['toetsmomentenGevorderde'] as $toetsmoment) {
                    $leerdoel->addToetsmomentGevorderde($toetsmoment);
                }
            }
            if (!empty($leerdoelData['toetsmomentenEindexamenniveau'])) {
                foreach ($leerdoelData['toetsmomentenEindexamenniveau'] as $toetsmoment) {
                    $leerdoel->addToetsmomentEindexamenniveau($toetsmoment);
                }
            }
            if (!empty($leerdoelData['toetsmomentenBovenEindexamenniveau'])) {
                foreach ($leerdoelData['toetsmomentenBovenEindexamenniveau'] as $toetsmoment) {
                    $leerdoel->addToetsmomentBovenEindexamenniveau($toetsmoment);
                }
            }

            $newone->addLeerdoel($leerdoel);
        }

        return $newone;
    }
}