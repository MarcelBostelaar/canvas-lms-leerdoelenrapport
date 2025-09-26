<?php

class LeerdoelResultaat{
    public $beschrijving;
    private $map;
    public function __construct() {
        $this->map = [];
    }
    public function add(Leerdoel $leerdoel, int $niveau, $datum) {
        $this->map[$leerdoel->naam] = ["niveau" => $niveau, "datum" => $datum, "leerdoel" => $leerdoel];
    }

    private function addInternalRep($item){
        $this->map[$item["leerdoel"]->naam] = $item;
    }

    public function getBehaaldNiveau(Leerdoel $leerdoel): int | null{
        return $this->map[$leerdoel->naam] ?? null;
    }

    public function getAll(){
        return $this->map;
    }

    public function fillWithZeroForMissing(array $leerdoelen){
        foreach($leerdoelen as $leerdoel){
            if(!isset($this->map[$leerdoel->naam])){
                $this->map[$leerdoel->naam] = ["niveau" => 0, "datum" => new DateTime("1970-01-01")];
            }
        }
    }

    private function getLatestDate() : DateTime {
        $latest = new DateTime("1970-01-01");
        foreach($this->map as $entry){
            if($entry["datum"] > $latest){
                $latest = $entry["datum"];
            }
        }
        return $latest;
    }

    /**
     * Sequentially combine results using the appropriate summation, allowing for insight of progression over time.
     * @param LeerdoelResultaat $nextResult
     * @return void
     */
    public function then(LeerdoelResultaat $nextResult) : LeerdoelResultaat{
        $leerdoelenA = array_keys($this->map);
        $leerdoelenB = array_keys($nextResult->map);
        $allLeerdoelen = array_unique(array_merge($leerdoelenA, $leerdoelenB));
        $newResult = new LeerdoelResultaat();

        $newResult->beschrijving = "Cumulatief tot: " . 
            $nextResult->getLatestDate()->format("Y-m-d") . 
            " - " . $nextResult->beschrijving;

        foreach($allLeerdoelen as $leerdoel){
            //check if they exist in both
            $entryA = $this->map[$leerdoel] ?? null;
            $entryB = $nextResult->map[$leerdoel] ?? null;
            if($entryA != null && $entryB != null){
                //Both exist, combine them according to the optelModel
                switch($entryA["leerdoel"]->optelModel){
                    case optelModel::Hoogste:
                        //Take the highest level
                        if($entryA["niveau"] >= $entryB["niveau"]){
                            $newResult->addInternalRep($entryA);
                        } else {
                            $newResult->addInternalRep($entryB);
                        }
                        break;

                    case optelModel::RunningAverage: //assuming 50% running average
                        $newNiveau = ($entryA["niveau"] + $entryB["niveau"])/2;
                        $newResult->add($entryB["leerdoel"], $newNiveau, $entryB["datum"]);
                        break;

                    case optelModel::Altijd:
                        //Take the lowest level
                        if($entryA["niveau"] < $entryB["niveau"]){
                            $newResult->addInternalRep($entryA);
                        } else {
                            $newResult->addInternalRep($entryB);
                        }
                        break;
                    case optelModel::Null:
                    default:
                        //No summation, just take the latest
                        throw new Exception("Unknown or Null optelModel for leerdoel '".$entryA["leerdoel"]->naam."'");
                }

            } else if($entryA != null){
                //Only in A
                $newResult->add($entryA["leerdoel"], $entryA["niveau"], $entryA["datum"]);
            } else if($entryB != null){
                //Only in B
                $newResult->add($entryB["leerdoel"], $entryB["niveau"], $entryB["datum"]);
            }
        }
        
        return $newResult;
    }
}