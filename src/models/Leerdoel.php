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

    public function __construct($categorie, $naam, $beginnerBeschrijving, $gevorderdeBeschrijving, $eindexamenniveauBeschrijving, $bovenEindniveauBeschrijving, $optelModel) {
        $this->categorie = $categorie;
        $this->naam = $naam;
        $this->beginnerBeschrijving = $beginnerBeschrijving;
        $this->gevorderdeBeschrijving = $gevorderdeBeschrijving;
        $this->eindexamenniveauBeschrijving = $eindexamenniveauBeschrijving;
        $this->bovenEindniveauBeschrijving = $bovenEindniveauBeschrijving;
        $this->optelModel = $optelModel;
    }
}