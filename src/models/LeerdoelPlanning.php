<?php


class LeerdoelPlanning{
    public $categorie;
    public $children = [];


    //TODO reimplement as flat collection that also can have subcategories, which are also LeerdoelPlanning objects.
    /** @var Leerdoel[] $leerdoelPlanning */
    private array $leerdoelPlanning = [];

    public function addLeerdoel(Leerdoel $leerdoel){
        $this->leerdoelPlanning[$leerdoel->naam] = $leerdoel;
    }

    public function getAll(){
        return $this->leerdoelPlanning;
    }

    public function addChildGroup(LeerdoelPlanning $leerdoelGroup){
        $this->children[ $leerdoelGroup->categorie] = $leerdoelGroup;
    }

    public function getLeerdoelByCanvasID($canvasID): ?Leerdoel{
        throw new Exception("Not implemented");
        foreach($this->leerdoelPlanning as $categorie => $leerdoelen){
            foreach($leerdoelen as $leerdoel ){
                if($leerdoel->id_in_canvas == $canvasID){
                    return $leerdoel;
                }
            }
        }
        return null;
    }

    public function getLeeruitkomstByCanvasID($canvasLeeruitkomstID): ?Leerdoel{
        throw new Exception("Not implemented");
        foreach($this->leerdoelPlanning as $categorie => $leerdoelen){
            foreach($leerdoelen as $leerdoel ){
                if($leerdoel->leeruitkomstIDInCanvas == $canvasLeeruitkomstID){
                    return $leerdoel;
                }
            }
        }
        return null;
    }

    public function getLeerdoelByName($name): ?Leerdoel{
        throw new Exception("Not implemented");
        foreach($this->leerdoelPlanning as $categorie => $leerdoelen){
            foreach($leerdoelen as $leerdoel){
                if($leerdoel->naam == $name){
                    return $leerdoel;
                }
            }
        }
        return null;
    }

    public function debugPopulateMissingToetsmomenten(){
        throw new Exception("Not implemented");
        foreach($this->leerdoelPlanning as $categorie => $leerdoelen){
            foreach($leerdoelen as $leerdoel){
                for($i = 1; $i <= $leerdoel->meesterschapsNiveau; $i++){
                    if(!isset($leerdoel->toetsmomenten[$i])){
                        $leerdoel->addToetsmoment($i, $i);
                    }
                }
            }
        }
    }
}