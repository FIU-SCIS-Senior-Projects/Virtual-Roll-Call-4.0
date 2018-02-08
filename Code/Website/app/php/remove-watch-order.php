<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$id = $request->id;
$connection = new DBHandler();
$result = $connection->removeWatchOrder($id);
//convert the response to a json object
die(json_encode($result));
