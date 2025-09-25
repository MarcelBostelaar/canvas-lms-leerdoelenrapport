<?php

class LeerdoelResultaat{
    public $beschrijving;
    private $map;
    public function __construct() {
        $this->map = [];
    }
    public function add(string $leerdoel, int $niveau, $datum) {
        $this->map[$leerdoel] = ["niveau" => $niveau, "datum" => $datum];
    }

    public function getBehaaldNiveau(string $leerdoel): int | null{
        return $this->map[$leerdoel] ?? null;
    }

    public function getAll(){
        return $this->map;
    }

    public function fillWithZeroForMissing(array $leerdoelen){
        foreach($leerdoelen as $leerdoel){
            if(!isset($this->map[$leerdoel])){
                $this->map[$leerdoel] = ["niveau" => 0, "datum" => new DateTime("1970-01-01")];
            }
        }
    }
}