<?php

class CourseRestricted implements ICacheSerialiserVisitor{
    public function serializeCanvasReader(CanvasReader $reader) : string {
        return "CanvasReader - " . $reader->getCourseURL() . " - " . $reader->getBaseURL();
    }
    public function serializeCanvasLeerdoelProvider(CanvasLeerdoelProvider $provider) : string
    {
        return $provider->serialize($this);
    }
    
    public function serializeLeerdoelenStructuurProvider(LeerdoelenStructuurProvider $provider) : string{
        return $provider->serialize($this);
    }
    public function serializeStudentProvider(StudentProvider $provider) : string{
        return $provider->serialize($this);
    }
    public function serializeGroupingProvider(GroupingProvider $provider) : string{
        return $provider->serialize($this);
    }
}