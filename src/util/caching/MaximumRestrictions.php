<?php

class MaximumRestrictions implements ICacheSerialiserVisitor{
    public function serializeCanvasReader(CanvasReader $reader) : string {
        return serialize($reader);
    }
    public function serializeCanvasLeerdoelProvider(CanvasLeerdoelProvider $provider) : string
    {
        return serialize($provider);
    }
    
    public function serializeLeerdoelenStructuurProvider(LeerdoelenStructuurProvider $provider) : string{
        return serialize($provider);
    }
    public function serializeStudentProvider(StudentProvider $provider) : string{
        return serialize($provider);
    }
    public function serializeGroupingProvider(GroupingProvider $provider) : string{
        return serialize($provider);
    }
    public function getValidity(): bool{
        return true; //Generated key always valid
    }
    public function signalSuccesfullyCached(){}//do nothing.
}