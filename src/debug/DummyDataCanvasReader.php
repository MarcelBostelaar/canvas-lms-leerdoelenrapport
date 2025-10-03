<?php

class DummyDataCanvasReader extends CanvasReader {
    private function DecorateMethod(callable $parentCall, $postProcessor, $name) {
        $result = $parentCall();
        // return $result;
        $prosessed = $postProcessor($result);
        ?>
        <pre>
=== Calling <?php echo $name; ?> ===
<?php var_dump($prosessed) ?>
        </pre>
        <?php
        return $prosessed;
    }

    public function fetchStudentSubmissions(int $studentID){
        $structure = (new LeerdoelenStructuurProvider($this))->getStructuur();

        return array_map(fn($i) =>
        [
            "workflow_state" => "graded",
            "full_rubric_assessment" => [
                "data" => Array_filter(array_map(fn($lo) => [
                    "points" => rand(0, $lo->meesterschapsNiveau),
                    "learning_outcome_id" => $lo->leeruitkomstIDInCanvas
                ], $structure->getAllLeerdoelen()), fn($_) => rand(1, 6) == 1)
            ],
            "assignment" => [
                "name" => "Fake outcome $i"
            ],
            "graded_at" => date_format(new DateTime("now"),"Y-m-d")
        ],
        range(0, rand(1, 4)));
    }

    //     return $this->DecorateMethod(fn() => 
    //     parent::fetchStudentSubmissions($studentID), fn($x) => filter_array_structure($x,
    //     [
    //         "workflow_state" => "somestring",
    //         "full_rubric_assessment" => [
    //             "data" => [
    //                 "points" => 1,
    //                 "learning_outcome_id" => 1
    //             ]
    //         ],
    //         "assignment" => [
    //             "name" => "somename"
    //         ],
    //         "graded_at" => "somedate"
    //     ]
    //      )
    //     , "fetchStudentSubmissions");
    // }

    // public function fetchStudentVakbeheersing(int $studentID){
    //     return $this->DecorateMethod(fn() => parent::fetchStudentVakbeheersing($studentID), "fetchStudentVakbeheersing");
    // }

    // public function fetchSections(){
    //     return $this->DecorateMethod(fn() => parent::fetchSections(), "fetchSections");
    // }

    public function fetchStudentsInSection($sectionID){
        return [[
    "id"=>
    42991,
    "name"=>
    "Test cursist"
        ]];
        // return $this->DecorateMethod(fn() => parent::fetchStudentsInSection($sectionID), "fetchStudentsInSection");
    }

    // public function fetchAllOutcomeGroups(){
    //     return $this->DecorateMethod(fn() => parent::fetchAllOutcomeGroups(), "fetchAllOutcomeGroups");
    // }

    // public function fetchOutcomesOfGroup($groupID){
    //     return $this->DecorateMethod(fn() => parent::fetchOutcomesOfGroup($groupID), "fetchOutcomesOfGroup");
    // }

    // public function fetchOutcome($id){
    //     return $this->DecorateMethod(fn() => parent::fetchOutcome($id), "fetchOutcome");
    // }

    
}

function filter_array_structure($input, $followStructure){
    if($followStructure === null){
        throw new Exception("No structure provided");
    }
    if(!is_array($input)){
        return $input;
    }
    if(array_is_list($input)){
        return array_map(fn($v) => filter_array_structure($v, $followStructure), $input);
    }
    $new_dict = [];
    foreach($input as $k => $v){
        if(!array_key_exists($k, $followStructure)){
            continue;
        }
        // if(is_array($v)){
        $new_dict[$k] = filter_array_structure($v, $followStructure[$k]);
        // } else {
        //     $new_dict[$k] = $v;
        // }
    }
    return $new_dict;
}