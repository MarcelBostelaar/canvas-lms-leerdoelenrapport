<?php


class LeerdoelPlanning{
    private $leerdoelPlanning = [];

    public function addLeerdoel(Leerdoel $leerdoel){
        if(!isset($this->leerdoelPlanning[$leerdoel->categorie])){
            $this->leerdoelPlanning[$leerdoel->categorie] = [];
        }
        array_push($this->leerdoelPlanning[$leerdoel->categorie], $leerdoel);
    }

    public function getAll(){
        return $this->leerdoelPlanning;
    }
}