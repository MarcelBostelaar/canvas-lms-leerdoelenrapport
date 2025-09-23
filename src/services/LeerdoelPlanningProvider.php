<?php

class LeerdoelPlanningProvider{

    public static function getPlanning() : LeerdoelPlanning {
        return self::loadFromFile();
    }

    private static function loadFromFile($filename = __DIR__ . '/../../data/leerdoelen.json') : LeerdoelPlanning {
        //dummy data
        $newone = new LeerdoelPlanning();
        $leerdoel1 = new Leerdoel("Categorie1", "Naam1", "Beginner beschrijving", "Gevorderde beschrijving", "Eindexamenniveau beschrijving", "Boven eindexamenniveau beschrijving", optelModel::Hoogste);
        $leerdoel2 = new Leerdoel("Categorie2", "Naam2", "Beginner beschrijving", "Gevorderde beschrijving", "Eindexamenniveau beschrijving", "Boven eindexamenniveau beschrijving", optelModel::RunningAverage);
        $leerdoel3 = new Leerdoel("Categorie2", "Naam3", "Beginner beschrijving", "Gevorderde beschrijving", "Eindexamenniveau beschrijving", "Boven eindexamenniveau beschrijving", optelModel::RunningAverage);
        $newone->addLeerdoel($leerdoel1);
        $newone->addLeerdoel($leerdoel2);
        $leerdoel1->addToetsmomentBeginner(1);
        $leerdoel1->addToetsmomentBeginner(2);
        $leerdoel1->addToetsmomentGevorderde(3);
        $leerdoel1->addToetsmomentEindexamenniveau(5);
        $leerdoel2->addToetsmomentBeginner(2);
        $leerdoel2->addToetsmomentGevorderde(4);
        $leerdoel2->addToetsmomentEindexamenniveau(6);
        $newone->addLeerdoel($leerdoel3);
        $leerdoel3->addToetsmomentBeginner(1);
        $leerdoel3->addToetsmomentGevorderde(3);
        $leerdoel3->addToetsmomentEindexamenniveau(4);


        return $newone;
    }
}