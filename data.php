<?php

// $fname = $_GET['firstname'];
//      if($fname=='Jeff')
//      {
//          //header("Content-Type: application/json");
//         echo $_GET['callback'] . '(' . "{'fullname' : 'Jeff Hansen'}" . ')';
//
//      }
//die;

require_once 'DataManager.php';

error_reporting(E_ALL);

$dataType = false;

$requiredParams = array(
    'dataType' => 'data_type',
);

$i = 1;

foreach ($requiredParams as $var => $name) {

    if (isset($_GET[$name])) {
        global $$var;
        $$var = $_GET[$name];
    } else if (isset($_POST[$name])) {
        global $$var;
        $$var = $_POST[$name];
    } else if (isset($argv[$i])) {
        global $$var;
        $$var = $argv[$i];
    }

    if ($$var === false) {
        DataManager::displayErrorMessage("Missing {$name} parameter!");
    }

    $i++;
}

$definedDataTypes = array(
    'bulbs', 'lanterns', 'roads',
);

$Data = new DataManager();

switch ($dataType) {
    case 'bulbs': {
            $results = $Data->getBulbs();
            break;
        }
    case 'lanterns': {
            $results = $Data->getLanterns();
            break;
        }
    case 'roads': {
            $results = $Data->getRoads();
            break;
        }
    default: {
            DataManager::displayErrorMessage("Invalid data_type parameter value!");
            break;
        }
}

DataManager::displayJsonResponse($results);