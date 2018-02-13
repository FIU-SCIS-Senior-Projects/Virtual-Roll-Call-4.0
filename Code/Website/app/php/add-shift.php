<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$shift_name = $request->sName;
$from_time = $request->fTime;
$to_time = $request->tTime;
$status = $request->sStatus;
$connection = new DBHandler();
$result = $connection->addShift($shift_name, $from_time, $to_time, $status);
//convert the response to a json object
die(json_encode($result));