<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$time = $request->time;
$connection = new DBHandler();
$result = $connection->updateTimeout($time);
//convert the response to a json object
die(json_encode($result));