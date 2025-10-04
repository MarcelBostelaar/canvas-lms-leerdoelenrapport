<?php

class Student{
    public $id;
    public $name;

    public Section $activeSection;

    public function __construct(int $id, string $naam, Section $activeSection){
        $this->id = $id;
        $this->name = $naam;
        $this->activeSection = $activeSection;
    }

    public function getMasteryResults(): LeerdoelResultaat{
        global $providers;
        return $providers->studentProvider->getStudentMasteryByID($this->id);
    }

    /**
     * Summary of getIndividualGrades
     * @param CanvasReader $canvasReader
     * @return LeerdoelResultaat[]
     */
    public function getIndividualGrades(): array{
        global $providers;
        return $providers->studentProvider->getStudentResultsByID($this->id);
    }
}