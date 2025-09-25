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

    public function getLeerdoelByCanvasID($canvasID){
        foreach($this->leerdoelPlanning as $categorie => $leerdoelen){
            foreach($leerdoelen as $leerdoel){
                if($leerdoel->id_in_canvas == $canvasID){
                    return $leerdoel;
                }
            }
        }
        return null;
    }

    public function getLeerdoelByName($name){
        foreach($this->leerdoelPlanning as $categorie => $leerdoelen){
            foreach($leerdoelen as $leerdoel){
                if($leerdoel->naam == $name){
                    return $leerdoel;
                }
            }
        }
        return null;
    }
}