<?php
require_once __DIR__ . "/utility/ConfigStructure.php";
class ConfigProvider{
    private static $filePath = "../data/config.json";
    public function getRawConfig(): Config {
        $json = file_get_contents(self::$filePath);
        $data = json_decode($json, true);
        if (!$data) {
            throw new \Exception("Failed to parse config file: " .self::$filePath);
        }


        $groupings = [];
        foreach ($data['sectionGrouping'] as $groupingName => $groupingData) {
            $periods = [];
            foreach ($groupingData['periods'] as $period => $periodData) {
                $periods[] = new Period((int)$period, $periodData['start'], $periodData['end']);
            }
            $sections = [];
            foreach ($groupingData['sections'] as $sectionName) {
                $sections[] = new Section($sectionName, null);
            }
            $groupings[$groupingName] = new SectionGrouping($groupingName, $sections, $periods); 
        }

        $outcomes = [];
        foreach ($data['outcomes'] as $outcomeData) {
            $outcomes[] = new Outcome(
                $outcomeData['naam'],
                $outcomeData['toetsmomenten'],
                $outcomeData['beschrijvingen']
            );
        }

        return new Config(new AllSectionGroupings($groupings), $outcomes);
    }
}