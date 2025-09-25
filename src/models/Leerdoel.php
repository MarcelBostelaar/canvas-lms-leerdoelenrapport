<?php

enum optelModel : string {
    case Hoogste = "Hoogste";
    case RunningAverage = "RunningAverage";
    case Altijd = "Altijd";
    case Null = "Null";
}

/**
 * Individueel leerdoel
 */
class Leerdoel{
    public $categorie = "";
    public $naam = "";
    public $beschrijvingen = [];
    public $toetsmomenten = [];
    PUBLIC $meesterschapsNiveau = null;
    public $optelModel = null;
    public $id_in_canvas = null;

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
