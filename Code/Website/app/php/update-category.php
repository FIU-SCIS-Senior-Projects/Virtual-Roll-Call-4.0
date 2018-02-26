<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$id = $request->id;
$name = $request->name;
$shift = $request->shifts;
$connection = new DBHandler();
$result = $connection->updateCategory($id, $name, $shift);
//convert the response to a json object
die(json_encode($result));