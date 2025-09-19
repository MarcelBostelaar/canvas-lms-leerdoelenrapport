<?php

enum optelModel {
    case Hoogste;
    case RunningAverage;
    case Altijd;
}

/**
 * Individueel leerdoel
 */
class Leerdoel{
    public $categorie;
    public $naam;
    public $beginnerBeschrijving;
    public $gevorderdeBeschrijving;
    public $eindexamenniveauBeschrijving;
    public $bovenEindniveauBeschrijving;
    public $optelModel;
    public $toetsmomentenBeginner = [];
    public $toetsmomentenGevorderde = [];
    public $toetsmomentenEindexamenniveau = [];

    public function __construct($categorie, $naam, $beginnerBeschrijving, $gevorderdeBeschrijving, $eindexamenniveauBeschrijving, $bovenEindniveauBeschrijving, $optelModel) {
        $this->categorie = $categorie;
        $this->naam = $naam;
        $this->beginnerBeschrijving = $beginnerBeschrijving;
        $this->gevorderdeBeschrijving = $gevorderdeBeschrijving;
        $this->eindexamenniveauBeschrijving = $eindexamenniveauBeschrijving;
        $this->bovenEindniveauBeschrijving = $bovenEindniveauBeschrijving;
        $this->optelModel = $optelModel;
    }

    public function addToetsmomentBeginner($periode) {
        array_push($this->toetsmomentenBeginner, $periode);
    }

    public function addToetsmomentGevorderde($periode) {
        array_push($this->toetsmomentenGevorderde, $periode);
    }

    public function addToetsmomentEindexamenniveau($periode) {
        array_push($this->toetsmomentenEindexamenniveau, $periode);
    }

    public function getToetsNiveauInPeriode($periode) : int{
        if(in_array($periode, $this->toetsmomentenEindexamenniveau)){
            return 3;
        }
        if(in_array($periode, $this->toetsmomentenGevorderde)){
            return 2;
        }
        if(in_array($periode, $this->toetsmomentenBeginner)){
            return 1;
        }
        return 0;
    }

    public function getLastToetsNiveauPeriode($niveau){
        if($niveau == 3){
            return empty($this->toetsmomentenEindexamenniveau) ? null : max($this->toetsmomentenEindexamenniveau);
        }
        if($niveau == 2){
            return empty($this->toetsmomentenGevorderde) ? null : max($this->toetsmomentenGevorderde);
        }
        if($niveau == 1){
            return empty($this->toetsmomentenBeginner) ? null : max($this->toetsmomentenBeginner);
        }
        return null;
    }

    public function getFirstToetsNiveauPeriode($niveau){
        if($niveau == 3){
            return empty($this->toetsmomentenEindexamenniveau) ? null : min($this->toetsmomentenEindexamenniveau);
        }
        if($niveau == 2){
            return empty($this->toetsmomentenGevorderde) ? null : min($this->toetsmomentenGevorderde);
        }
        if($niveau == 1){
            return empty($this->toetsmomentenBeginner) ? null : min($this->toetsmomentenBeginner);
        }
        return null;
    }
}
