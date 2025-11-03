<?php
require_once __DIR__ . '/../services/StudentProvider.php';

class DummyStudentResultProvider extends StudentProvider{
    public function getStudentResultsByID($studentID): array{
        return cached_call(new StudentIDRightsAPIKeyRestricted($studentID), 3600,
        fn() => $this->_getStudentResultsByID($studentID),
         "getStudentResultsByID", $studentID);
    }
    public function _getStudentResultsByID($studentID): array{
        //Return dummy data
        global $providers;
        $structuur = $providers->leerdoelenStructuurProvider->getStructuur();
        $firstdate = (new DateTime())->sub(new DateInterval("P1D"));
        $result = [];
        $resultAmounts = rand(3, 5);
        $perfectStudent = rand(0, 10) == 0;
        for($i=0; $i<$resultAmounts; $i++){
            $res = new LeerdoelResultaat();
            $res->beschrijving = "Dummy resultaat " . ($i+1);
            $date = (clone $firstdate)->sub(new DateInterval("P" . ($i) . "D"));
            foreach($structuur->getAllLeerdoelen() as $leerdoel){
                if($perfectStudent){
                    $res->add($leerdoel, $leerdoel->meesterschapsNiveau, $date);
                    continue;
                }
                if(rand(0,2) == 0) continue; //Skip some leerdoelen
                $res->add($leerdoel, rand(0, $leerdoel->meesterschapsNiveau), $date);
            }
            $result[] = $res;
        }
        return $result;
    }

    public function getStudentMasteryByID($studentID): LeerdoelResultaat{
        return cached_call(new StudentIDRightsAPIKeyRestricted($studentID), 3600,
        fn() => $this->_getStudentMasteryByID($studentID),
         "getStudentMasteryByID", $studentID);
    }

    public function _getStudentMasteryByID($studentID): LeerdoelResultaat{
        global $providers;
        $structuur = $providers->leerdoelenStructuurProvider->getStructuur();
        
        $regularResults = $this->getStudentResultsByID($studentID);
        if(count($regularResults) == 0){
            $newone = new LeerdoelResultaat(); //No results at all
            $newone->fillWithZeroForMissing($structuur->getAllLeerdoelen());
            return $newone;
        }
        $regularResults = array_map(fn($x) => ["date"=>$x->getLatestDate(), "result"=>$x], $regularResults);
        usort($regularResults, fn($a, $b) => $a["date"] <=> $b['date']);
        $regularResults = array_map(fn($x) => $x["result"], $regularResults);
        while(count($regularResults) > 1){
            $a = array_shift($regularResults);
            $b = array_shift($regularResults);
            $regularResults = array_merge([$a->then($b)], $regularResults);
        }
        $regularResults[0]->fillWithZeroForMissing($structuur->getAllLeerdoelen());
        $regularResults[0]->beschrijving = "Totaal vakbeheersing (Dummy data)";
        return $regularResults[0];
    }
}