<?php
include_once __DIR__ . "/ConfigProvider.php";
include_once __DIR__ . "/../util/UtilFuncs.php";
require_once __DIR__ . '/../util/caching/ICacheSerialisable.php';
require_once __DIR__ . '/../util/caching/CourseRestricted.php';
class UncachedGroupingProvider{
    protected CanvasReader $canvasReader;
    public function __construct( CanvasReader $canvasReader ){
        $this->canvasReader = $canvasReader;
    }

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

class GroupingProvider extends UncachedGroupingProvider implements ICacheSerialisable{
    public function serialize(ICacheSerialiserVisitor $visitor): string
    {
        return $visitor->serializeGroupingProvider($this);
    }

    public function getSectionGroupings(): AllSectionGroupings{
        global $sharedCacheTimeout;
        //Maximally restricted to single api keys, so that each teacher only gets the sections and students they are allowed to see.
        $data = cached_call(new MaximumRestrictions(), $sharedCacheTimeout,
        fn() => parent::getSectionGroupings(), $this,
        "getSectionGroupings");

        //pre-whitelist this api key for access to data for these students, to enable sharing of data between users
        foreach($data->getAllSections() as $section){
            foreach($section->getStudents() as $student){
                whitelist_apikey_for_student_id($this->canvasReader->getApiKey(), $student->id);
            }
        }
        return $data;
    }

    public function getCanvasReader(){
        return $this->canvasReader;
    }
}