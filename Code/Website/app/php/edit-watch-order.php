<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$id = $request->id;
$desc = $request->desc;
$address = $request->address;
$lat = $request->lat;
$lng = $request->lng;
$expDate = $request->expDate;
$connection = new DBHandler();
$result = $connection->editWatchOrder($id, $desc, $address, $lat, $lng, $expDate);
//convert the response to a json object
die(json_encode($result));
