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
    public $optelModel = optelModel::Null;
    public $id_in_canvas = null;
    public $leeruitkomstIDInCanvas = null;

    public function addToetsMoment($niveau, $periode){
        if(!isset($this->toetsmomenten[$niveau-1])){
            $this->toetsmomenten[$niveau-1] = [];
        }
        array_push($this->toetsmomenten[$niveau-1], $periode);
    }

    public function addBeschrijving($niveau, $beschrijving){
        $this->beschrijvingen[$niveau-1] = $beschrijving;
    }

    public function getToetsNiveauInPeriode($periode) : int{
        foreach($this->toetsmomenten as $niveau => $periodes){
            if(in_array($periode, $periodes)){
                return $niveau + 1;
            }
        }
        return 0;
    }

    public function getLastToetsNiveauPeriode($niveau){
        if(!isset($this->toetsmomenten[$niveau - 1]) || empty($this->toetsmomenten[$niveau - 1])){
            return null;
        }
        return max($this->toetsmomenten[$niveau - 1]);
    }

    public function getFirstToetsNiveauPeriode($niveau){
        if(!isset($this->toetsmomenten[$niveau - 1]) || empty($this->toetsmomenten[$niveau - 1])){
            return null;
        }
        return min($this->toetsmomenten[$niveau - 1]);
    }
}
