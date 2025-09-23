<?php

class MeesterschapProvider{
    private $canvasReader;

    public function __construct(CanvasReader $canvasReader) {
        $this->canvasReader = $canvasReader;
    }

    public function getMeesterschap($studentID) : LeerdoelResultaat{
        $student = $this->canvasReader->fetchStudentVakbeheersing($studentID);
        //transform naar LeerdoelResultaat met hulp van leerdoelplanning
        return $student->resultaten;
    }
}