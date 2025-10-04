<?php

class SingleResult{
    public int | float $niveau;
    public DateTime $datum;
    public Leerdoel $leerdoel;
    public function __construct(int | float $niveau, DateTime $datum, Leerdoel $leerdoel){
        $this->niveau = $niveau;
        $this->datum = $datum;
        $this->leerdoel = $leerdoel;
    }
}

class LeerdoelResultaat{
    public $beschrijving;
    /**
     * Summary of map
     * @var SingleResult[]
     */
    private array $map;
    public function __construct() {
        $this->map = [];
    }
    public function add(Leerdoel $leerdoel, int $niveau, $datum) {
        if(is_numeric($datum)) {
            $oldDate = $datum;
            $datum = new DateTime();
            $datum->setTimestamp($oldDate);
        }
        $this->map[$leerdoel->naam] = new SingleResult($niveau, $datum, $leerdoel);
    }

    public function getBehaaldNiveau(Leerdoel $leerdoel): int | float | null{
        $value = $this->map[$leerdoel->naam] ?? null;
        if($value != null){
            global $roundToNearestOneOver; 
            //rounding. IE setting of 2 will round to nearest 0.5
            //setting of 4 will round to nearest 0.25
            //setting of 1 will round to nearest whole number
            return roundToNearestFraction($value->niveau, $roundToNearestOneOver);
        }
        return null;
    }

    public function fillWithZeroForMissing(array $leerdoelen){
        foreach($leerdoelen as $leerdoel){
            if(!isset($this->map[$leerdoel->naam])){
                $this->add($leerdoel, 0, new DateTime("1970-01-01"));
            }
        }
    }

    public function getLatestDate() : DateTime {
        $latest = new DateTime("1970-01-01");
        foreach($this->map as $entry){
            if($entry->datum > $latest){
                $latest = $entry->datum;
            }
        }
        return $latest;
    }

    /**
     * Summary of getAll
     * @return SingleResult[]
     */
    public function getAll() : array {
        return $this->map;
    }

    private function addExisting(SingleResult $result) {
        $this->map[$result->leerdoel->naam] = $result;
    }

    /**
     * Sequentially combine results using the appropriate summation, allowing for insight of progression over time.
     * @param LeerdoelResultaat $nextResult
     * @return void
     */
    public function then(LeerdoelResultaat $nextResult) : LeerdoelResultaat{
        // echo "Did it once";
        $leerdoelenA = array_keys($this->map);
        $leerdoelenB = array_keys($nextResult->map);
        $allLeerdoelen = array_unique(array_merge($leerdoelenA, $leerdoelenB));
        $newResult = new LeerdoelResultaat();

        $newResult->beschrijving = "Cumulatief tot: " . 
            $nextResult->getLatestDate()->format("Y-m-d") . 
            " - " . $nextResult->beschrijving;

        foreach($allLeerdoelen as $leerdoel){
            // var_dump($leerdoel);
            //check if they exist in both
            $entryA = $this->map[$leerdoel] ?? null;
            $entryB = $nextResult->map[$leerdoel] ?? null;
            if($entryA != null && $entryB != null){
                $leerdoel = $entryA->leerdoel;
                // var_dump($leerdoel);
                //Both exist, combine them according to the optelModel
                switch($leerdoel->optelModel){
                    case optelModel::Hoogste:
                        //Take the highest level
                        if($entryA->niveau >= $entryB->niveau){
                            $newResult->addExisting($entryA);
                        } else {
                            $newResult->addExisting($entryB);
                        }
                        break;

                    case optelModel::RunningAverage: //assuming 50% running average
                        $percentageLast = $leerdoel->runningAverageValue / 100;
                        $newNiveau = $entryA->niveau * $percentageLast + $entryB->niveau * (1-$percentageLast);
                        $newResult->add($leerdoel, $newNiveau, $entryB->datum);
                        break;

                    case optelModel::Gemiddelde:
                        $newNiveau = ($entryA->niveau + $entryB->niveau)/2;
                        $newResult->add($leerdoel, $newNiveau, $entryB->datum);
                        break;
                    case optelModel::Null:
                    default:
                        //No summation, just take the latest
                        throw new Exception("Unknown or Null optelModel for leerdoel '$leerdoel->naam'");
                }

            } else if($entryA != null){
                //Only in A
                $newResult->addExisting($entryA);
            } else if($entryB != null){
                //Only in B
                $newResult->addExisting($entryB);
            }
        }
        
        return $newResult;
    }
}