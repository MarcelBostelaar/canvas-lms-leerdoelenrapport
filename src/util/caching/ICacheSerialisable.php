<?php
interface ICacheSerialiserVisitor{
    public function serializeCanvasReader(CanvasReader $reader) : string;
    public function serializeCanvasLeerdoelProvider(CanvasLeerdoelProvider $provider) : string;
    public function serializeLeerdoelenStructuurProvider(LeerdoelenStructuurProvider $provider) : string;
    public function serializeStudentProvider(StudentProvider $provider) : string;
    public function serializeGroupingProvider(GroupingProvider $provider) : string;
    /**
     * Returns whether or not the key generated using these rules is valid. True if cached value may be returned, false if unknown or disallowed.
     * @arg $key For error tracking purposes
     * @return void
     */
    public function getValidity($key): bool;
    /**
     * Post-caching signal in which the caching rule implementation can perform any extra operations.
     * @return void
     */
    public function signalSuccesfullyCached();

}
interface ICacheSerialisable{
    public function serialize(ICacheSerialiserVisitor $visitor): string;
}