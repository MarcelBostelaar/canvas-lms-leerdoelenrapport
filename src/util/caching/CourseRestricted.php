<?php

class CourseRestricted implements ICacheSerialiserVisitor{
    public function serializeCanvasReader(CanvasReader $reader) : string {
        return "CanvasReader - " . $reader->getCourseURL() . " - " . $reader->getBaseURL();
    }
    public function serializeCanvasLeerdoelProvider(CanvasLeerdoelProvider $provider) : string
    {
        return "CanvasLeerdoelProvider - " . $this->serializeCanvasReader($provider->getCanvasReader());
    }
    public function serializeLeerdoelenStructuurProvider(LeerdoelenStructuurProvider $provider) : string{
        return "LeerdoelenStructuurProvider - " . $this->serializeCanvasReader($provider->getCanvasReader());
    }
    public function serializeStudentProvider(StudentProvider $provider) : string{
        return "StudentProvider - " . $this->serializeCanvasReader($provider->getCanvasReader());
    }
    public function serializeGroupingProvider(GroupingProvider $provider) : string{
        return "GroupingProvider - " . $this->serializeCanvasReader($provider->getCanvasReader());
    }
    public function getValidity($key): bool{
        return true; //Generated key always valid
    }
    public function signalSuccesfullyCached(){}//do nothing.
}