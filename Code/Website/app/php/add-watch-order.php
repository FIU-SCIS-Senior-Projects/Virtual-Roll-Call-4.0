<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$desc = $request->desc;
$address = $request->address;
$lat = $request->lat;
$long = $request->long;
$expDate = $request->expDate;
date_default_timezone_set('America/New_York');
$addDate= date('Y-m-d');
$connection = new DBHandler();
$result = $connection->addWatchOrder($desc, $address, $lat, $long, $addDate, $expDate);
//convert the response to a json object
die(json_encode($result));
