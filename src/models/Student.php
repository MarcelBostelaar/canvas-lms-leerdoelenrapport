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

    public function getMasteryResults(CanvasReader $canvasReader): LeerdoelResultaat{
        return (new StudentProvider($canvasReader))->getStudentMasteryByID($this->id);
    }

    /**
     * Summary of getIndividualGrades
     * @param CanvasReader $canvasReader
     * @return LeerdoelResultaat[]
     */
    public function getIndividualGrades(CanvasReader $canvasReader): array{
        return (new StudentProvider($canvasReader))->getStudentResultsByID($this->id);
    }
}