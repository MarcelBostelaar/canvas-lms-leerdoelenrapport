<?php
interface ICacheSerialiserVisitor{
    public function serializeCanvasReader(CanvasReader $reader) : string;
    public function serializeCanvasLeerdoelProvider(CanvasLeerdoelProvider $provider) : string;
    public function serializeLeerdoelenStructuurProvider(LeerdoelenStructuurProvider $provider) : string;
    public function serializeStudentProvider(StudentProvider $provider) : string;
    public function serializeGroupingProvider(GroupingProvider $provider) : string;

}
interface ICacheSerialisable{
    public function serialize(ICacheSerialiserVisitor $visitor): string;
}