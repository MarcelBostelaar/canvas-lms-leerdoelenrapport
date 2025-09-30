<?php
include_once __DIR__ . "/ConfigProvider.php";
include_once __DIR__ . "/../util/UtilFuncs.php";
class GroupingProvider implements ICacheSerialisable{
    private CanvasReader $canvasReader;
    public function __construct( CanvasReader $canvasReader ){
        $this->canvasReader = $canvasReader;
    }

    public function serialize(ICacheSerialiserVisitor $visitor): string
    {
        return "GroupingProvider - " . $visitor->serializeCanvasReader(reader: $this->canvasReader);
    }

    //TODO cache
    public function getSectionGroupings(): AllSectionGroupings {
        $unlinkedGroupings = (new ConfigProvider())->getRawConfig()->sectionGroupings;
        $sectionData = $this->canvasReader->fetchSections();
        $indexedSections = [];
        foreach($sectionData as $section){
            $indexedSections[$section["name"]] = $section["id"];
        }
        foreach($unlinkedGroupings->getAllSections() as $section){
            if(!isset($indexedSections[$section->name])){
                throw new Exception("Section " . $section->name . " not found in Canvas");
            }
            $section->canvasID = $indexedSections[$section->name];
            $studentsInSection = $this->canvasReader->fetchStudentsInSection($section->canvasID);
            foreach($studentsInSection as $student){
                $section->addStudent($student["id"], $student["name"]);
            }
        }
        return $unlinkedGroupings;
    }
}