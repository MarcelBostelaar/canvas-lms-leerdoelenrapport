<?php
interface ICacheSerialiserVisitor{
    public function serializeCanvasReader(CanvasReader $reader) : string;
    public function serializeCanvasLeerdoelProvider(CanvasLeerdoelProvider $provider) : string;
    public function serializeLeerdoelenStructuurProvider(LeerdoelenStructuurProvider $provider) : string;
    public function serializeStudentProvider(StudentProvider $provider) : string;
}
abstract class ICacheSerialisable{

    public function serialize(ICacheSerialiserVisitor $visitor): string{
        return serialize($this);
    }
}