<?php

class Period {
    public int $period;
    public DateTime $start;
    public DateTime $end;
    public function __construct(int $period, string $start, string $end) {
        $this->period = $period;
        $this->start = new DateTime($start);
        $this->end = new DateTime($end);
    }
}

class Section{
    public string $name;
    public int | null $canvasID;

    /**
     * Array of arrays, each containing a student ID and name
     * @var array[]
     */
    private array $students = [];

    public function __construct(string $name, int|null $canvasID = null){
        $this->name = $name;
        $this->canvasID = $canvasID;
    }

    public function addStudent(int $id, string $name){
        $this->students[$id] = new Student($id, $name, $this);
    }

    /**
     * 
     * @return Student[]
     */
    public function getStudents(CanvasReader $canvasReader) : array{
        return $this->students;
    }
}

class SectionGrouping {
    public string $name;
    /**
     * Names of the sections found in canvas
     * @var Section[]
     */
    public array $sections; // array of section names
    /**
     * 
     * @var Period[]
     */
    public array $periods;  // array of Period objects, keyed by period number
    public function __construct(string $name, array $sections, array $periods) {
        $this->name = $name;
        $this->sections = $sections;
        $this->periods = $periods;
    }

    public function getPeriodOnDate(DateTime $date): Period|null {
        foreach($this->periods as $period){
            if($date >= $period->start && $date <= $period->end){
                return $period;
            }
        }
        return null;
    }
}

class AllSectionGroupings{
    /**
     * Keyed by year
     * @var SectionGrouping[]
     */
    private $data;

    public function __construct(array $data){
        $this->data = $data;
    }

    public function getAllSections(): array{
        $merged = [];
        foreach($this->data as $_ => $grouping){
            $merged = array_merge($merged, $grouping->sections);
        }
        return $merged;
    }

    public function getAllGroupings(): array{
        return $this->data;
    }

    public function getStudent($id, CanvasReader $canvasReader): Student{
        foreach($this->data as $_ => $grouping){
            foreach($grouping->sections as $section){
                if(isset($section->getStudents(canvasReader: $canvasReader)[$id])){
                    return $section->getStudents($canvasReader)[$id];
                }
            }
        }
        throw new Exception("Student with ID $id not found in any section");
    }
}
