<?php

class LeerdoelPlanning implements IteratorAggregate {
    private $map;

    public function __construct() {
        $this->map = [];
    }

    public function add(Leerdoel $leerdoel, $periodeBeginner, $periodeGevorderde, $periodeEindexamenniveau) {
        $this->map[$leerdoel->naam] = [
            'b' => $periodeBeginner,
            'g' => $periodeGevorderde,
            'e' => $periodeEindexamenniveau
        ];
    }

    public static function loadFromFile() : LeerdoelPlanning {
        $planning = new LeerdoelPlanning();
        // Dummy data for now
        $leerdoel = new Leerdoel("Categorie1", "Naam1", "Beginner beschrijving", "Gevorderde beschrijving", "Eindexamenniveau beschrijving", "Boven eindexamenniveau beschrijving", optelModel::Hoogste);
        $planning->add($leerdoel, 2, 5, 7);
        $leerdoel2 = new Leerdoel("Categorie2", "Naam2", "Beginner beschrijving", "Gevorderde beschrijving", "Eindexamenniveau beschrijving", "Boven eindexamenniveau beschrijving", optelModel::RunningAverage);
        $planning->add($leerdoel2, 2, 4, 10);
        return $planning;
    }

    public function getIterator(): Traversable {
        return new ArrayIterator($this->map);
    }
}