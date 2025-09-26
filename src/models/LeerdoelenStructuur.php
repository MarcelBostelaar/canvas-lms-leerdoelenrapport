<?php


class LeerdoelenStructuur{
    public $categorie;
    public $children = [];
/** @var Leerdoel[] $leerdoelPlanning */
    private array $leerdoelPlanning = [];

    public function addLeerdoel(Leerdoel $leerdoel){
        $this->leerdoelPlanning[$leerdoel->naam] = $leerdoel;
    }

    public function getAll(){
        return $this->leerdoelPlanning;
    }

    public function addChildGroup(LeerdoelenStructuur $leerdoelGroup){
        $this->children[ $leerdoelGroup->categorie] = $leerdoelGroup;
    }

    public function getLeerdoelByCanvasID($canvasID): ?Leerdoel{
        foreach($this->leerdoelPlanning as $leerdoel){
            if($leerdoel->id_in_canvas == $canvasID){
                return $leerdoel;
            }
        }
        foreach($this->children as $childGroup){
            $result = $childGroup->getLeerdoelByCanvasID($canvasID);
            if($result !== null){
                return $result;
            }
        }
        return null;
    }

    public function getLeeruitkomstByCanvasID($canvasLeeruitkomstID): ?Leerdoel{
        foreach($this->leerdoelPlanning as $leerdoel){
            if($leerdoel->leeruitkomstIDInCanvas == $canvasLeeruitkomstID){
                return $leerdoel;
            }
        }
        foreach($this->children as $childGroup){
            $result = $childGroup->getLeeruitkomstByCanvasID($canvasLeeruitkomstID);
            if($result !== null){
                return $result;
            }
        }
        return null;
    }

    public function getLeerdoelByName($name): ?Leerdoel{
        foreach($this->leerdoelPlanning as $leerdoel){
            if($leerdoel->naam == $name){
                return $leerdoel;
            }
        }
        foreach($this->children as $childGroup){
            $result = $childGroup->getLeerdoelByName($name);
            if($result !== null){
                return $result;
            }
        }
        return null;
    }

    public function debugPopulateMissingToetsmomenten(){
        foreach($this->leerdoelPlanning as $leerdoel){
            for($i = 1; $i <= $leerdoel->meesterschapsNiveau; $i++){
                if(!isset($leerdoel->toetsmomenten[$i])){
                    $leerdoel->addToetsmoment($i, $i);
                }
            }
        }
    }
}