<?php
include_once __DIR__ . "/ConfigProvider.php";
include_once __DIR__ . "/../util/UtilFuncs.php";
include_once __DIR__ . "/../util/caching/TeacherCourseRestricted.php";
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
        return "GroupingProvider - " . $visitor->serializeCanvasReader(reader: $this->canvasReader);
    }

    public function getSectionGroupings(): AllSectionGroupings{
        global $sharedCacheTimeout;
        return cached_call(new TeacherCourseRestricted(), $sharedCacheTimeout,
        fn() => parent::getSectionGroupings(), $this,
        "getSectionGroupings");
    }
}