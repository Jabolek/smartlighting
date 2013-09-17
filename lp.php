<?php

require_once 'LPC.php';

error_reporting(E_ALL);

$roadCoords = false;
$roadRadius = false;
$bulbRadius = false;
$enableScaling = 1;
$outputType = 1;

$requiredParams = array(
    'roadCoords' => 'road_coords',
    'roadRadius' => 'road_radius',
    'bulbRadius' => 'bulb_radius',
    'enableScaling' => 'enable_scaling',
    'outputType' => 'output_type',
);

$i = 1;

foreach ($requiredParams as $var => $name) {

    if (isset($_GET[$name])) {
        global $$var;
        $$var = $_GET[$name];
    } else if (isset($_POST[$name])) {
        global $$var;
        $$var = $_POST[$name];
    } else if(isset($argv[$i])){
        global $$var;
        $$var = $argv[$i];
    }

    if ($$var === false) {
        LanternPositionsCalculator::displayErrorMessage("Missing {$name} parameter!");
    }
    
    $i++;
}

$enableScaling = (int) $enableScaling;
$outputType = (int) $outputType;

$Calc = new LanternPositionsCalculator($enableScaling);

$positions = $Calc->calculateLanternPositions($roadCoords, $roadRadius, $bulbRadius);

if ($outputType == 1) {
    LanternPositionsCalculator::displayJsonResponse($positions);
} else {
    LanternPositionsCalculator::displayTsvResponse($positions);
}