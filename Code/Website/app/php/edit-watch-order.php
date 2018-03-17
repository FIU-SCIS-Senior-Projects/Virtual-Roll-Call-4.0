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
$startDate = $request->startDate;
$startTime = $request->startTime;
$expTime = $request->expTime;
$zone = $request->zone; 
$businessName = $request->businessName;
$ownerName = $request->ownerName; 
$woRequester = $request->woRequester; 
$phone = $request->phone;
$woInstruction = $request->woInstruction;
$eName = $request->eName;
$eAddress = $request->eAddress;
$ePhone = $request->ePhone;

$connection = new DBHandler();
$result = $connection->editWatchOrder($id, $desc, $address, $lat, $lng, $expDate, $startDate, $startTime, $expTime, $zone, $businessName,                                  $ownerName, $woRequester, $phone, $woInstruction, $eName, $eAddress, $ePhone);
//convert the response to a json object
die(json_encode($result));
