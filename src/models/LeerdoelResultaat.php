<?php
enum Niveau: Int {
    case NietBehaald = 0;
    case Beginner = 1;
    case Gevorderde = 2;
    case Eindexamenniveau = 3;
    case BovenEindexamenniveau = 4;
}
class LeerdoelResultaat{
    public $map;
    public function __construct() {
        $this->map = [];
    }
    public function add(string $leerdoel, niveau $niveau) {
        $this->map[$leerdoel] = $niveau;
    }

    public function getBehaaldNiveau(string $leerdoel): Niveau {
        return $this->map[$leerdoel] ?? Niveau::NietBehaald;
    }
}