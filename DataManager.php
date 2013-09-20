<?php

class DataManager {

    function __construct() {
        
    }

    public static function displayJsonResponse($data) {

        header('Content-type: application/json; charset=utf8', true);
        
        echo json_encode($data);
    }

    function getBulbs() {

        $bulbs = $this->getCsvFileContents('bulbs.csv');

        return $bulbs;
    }

    function getLanterns() {

        $lanterns = $this->getCsvFileContents('lanterns.csv');

        return $lanterns;
    }

    function getRoads() {

        $handle = opendir('data/roads/');

        $files = array();

        while (($entry = readdir($handle)) !== false) {
            if ($entry != "." && $entry != "..") {
                $files[] = $entry;
            }
        }

        closedir($handle);

        $roads = array();

        foreach ($files as $f) {
            $roads[] = $this->getRoadData($f);
        }

        return $roads;
    }

    private function getRoadData($filename) {

        $fp = fopen("data/roads/{$filename}", 'r');

        $name = trim(fgets($fp));
        $width = (float) trim(fgets($fp));

        $coords = array();

        while ($line = fgets($fp)) {

            $arr = explode(',', $line);

            $coords[] = array(
                'x' => (float) $arr[0],
                'y' => (float) $arr[1],
            );
        }

        $data = array(
            'name' => $name,
            'width' => $width,
            'coords' => $coords,
        );

        return $data;
    }

    private function getCsvFileContents($filename) {

        $fp = fopen("data/{$filename}", 'r');

        $headers = fgetcsv($fp);

        $data = array();

        while ($line = fgetcsv($fp)) {

            $dataLine = array();

            foreach ($headers as $i => $header) {
                $dataLine[$header] = $line[$i];
            }

            $data[] = $dataLine;
        }

        return $data;
    }

}