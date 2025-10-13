<?php
require_once __DIR__ . '/../util/UtilFuncs.php';

class LeerdoelenStructuur{
    public $categorie;
    public $children = [];
/** @var Leerdoel[] $leerdoelPlanning */
    private array $leerdoelPlanning = [];

    public function addLeerdoel(Leerdoel $leerdoel){
        $this->leerdoelPlanning[$leerdoel->naam] = $leerdoel;
    }

    public function updateLeerdoel(Leerdoel $leerdoel){
        if(isset($this->leerdoelPlanning[$leerdoel->naam])){
            $this->leerdoelPlanning[$leerdoel->naam] = $leerdoel;
        }
        else{
            foreach($this->children as $child){
                if($child->updateLeerdoel($leerdoel)){
                    return true;
                };
            }
        }
        return false;
    }

    /**
     * Summary of getNonChildLeerdoelen
     * @return Leerdoel[]
     */
    public function getNonChildLeerdoelen(): array{
        return $this->leerdoelPlanning;
    }

    /**
     * Summary of getChildren
     * @return LeerdoelenStructuur[]
     */
    public function getChildren(): array{
        return $this->children;
    }

    /**
     * Summary of getAllLeerdoelen
     * @return Leerdoel[]
     */
    public function getAllLeerdoelen(){
        return arrayTurboFlattener($this->leerdoelPlanning, ...array_map(fn($child) => $child->getAllLeerdoelen(), $this->children));
    }

    public function addChildGroup(LeerdoelenStructuur $leerdoelGroup){
        $this->children[ $leerdoelGroup->categorie] = $leerdoelGroup;
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

    public function deleteLeerdoel(int $canvasID){
        foreach($this->leerdoelPlanning as $key => $leerdoel){
            if($leerdoel->leeruitkomstIDInCanvas == $canvasID){
                unset($this->leerdoelPlanning[$key]);
            }
        }
        foreach($this->children as $childGroup){
            $childGroup->deleteLeerdoel($canvasID);
        }
    }

    public function debugPopulateMissingToetsmomenten(){
        foreach($this->getAllLeerdoelen() as $leerdoel){
            for($i = 1; $i <= $leerdoel->meesterschapsNiveau; $i++){
                if(!isset($leerdoel->toetsmomenten[$i])){
                    $leerdoel->addToetsmoment($i, $i);
                }
            }
        }
    }
}