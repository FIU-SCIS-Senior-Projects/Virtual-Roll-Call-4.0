<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$lat = $request->lat;
$lon = $request->lon;
$connection = new DBHandler();
$result = $connection->updateLatLong($lat, $lon);
//convert the response to a json object
die(json_encode($result));