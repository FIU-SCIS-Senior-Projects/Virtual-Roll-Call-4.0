<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$from = $request->from;
$to = $request->to;
$connection = new DBHandler();
$result = $connection->deleteArchive($from, $to);
//convert the response to a json object

die(json_encode($result));