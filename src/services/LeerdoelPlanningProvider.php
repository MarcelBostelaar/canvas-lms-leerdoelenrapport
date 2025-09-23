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
        $newone[$leerdoel1->naam] = $leerdoel1;
        $newone[$leerdoel2->naam] = $leerdoel2;
        $leerdoel1->addToetsmomentBeginner(1);
        $leerdoel1->addToetsmomentBeginner(2);
        $leerdoel1->addToetsmomentGevorderde(3);
        $leerdoel1->addToetsmomentEindexamenniveau(5);
        $leerdoel2->addToetsmomentBeginner(2);
        $leerdoel2->addToetsmomentGevorderde(4);
        $leerdoel2->addToetsmomentEindexamenniveau(6);


        return $newone;
        
        $jsonString = file_get_contents($filename);
        $data = json_decode($jsonString, true);

        $instance = new LeerdoelPlanning();
        $instance->leerdoelPlanning = $data;
        return $instance;
    }
}