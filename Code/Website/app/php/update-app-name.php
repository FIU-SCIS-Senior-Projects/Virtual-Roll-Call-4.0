<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$name = $request->name;
$connection = new DBHandler();
$result = $connection->updateAppName($name);
//convert the response to a json object
die(json_encode($result));