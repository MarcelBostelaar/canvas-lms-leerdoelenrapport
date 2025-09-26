<?php

enum optelModel : string {
    case Hoogste = "Hoogste";
    case RunningAverage = "RunningAverage";
    case Gemiddelde = "Gemiddelde";
    case Null = "Null";
}

/**
 * Individueel leerdoel
 */
class Leerdoel{
    public $naam = "";
    public $beschrijvingen = [];
    public $toetsmomenten = [];
    public $meesterschapsNiveau = null;
    public $optelModel = optelModel::Null;
    public $runningAverageValue = null;
    public $leeruitkomstIDInCanvas = null;

    public function addToetsMoment($niveau, $periode){
        if(!isset($this->toetsmomenten[$niveau])){
            $this->toetsmomenten[$niveau] = [];
        }
        array_push($this->toetsmomenten[$niveau], $periode);
    }

    public function addBeschrijving($niveau, $beschrijving){
        $this->beschrijvingen[$niveau] = $beschrijving;
    }

    public function getToetsNiveauInPeriode($periode) : int{
        foreach($this->toetsmomenten as $niveau => $periodes){
            if(in_array($periode, $periodes)){
                return $niveau;
            }
        }
        return 0;
    }

    public function getLastToetsNiveauPeriode($niveau){
        if(!isset($this->toetsmomenten[$niveau]) || empty($this->toetsmomenten[$niveau])){
            return null;
        }
        return max($this->toetsmomenten[$niveau]);
    }

    public function getFirstToetsNiveauPeriode($niveau){
        if(!isset($this->toetsmomenten[$niveau]) || empty($this->toetsmomenten[$niveau])){
            return null;
        }
        return min($this->toetsmomenten[$niveau]);
    }
}
