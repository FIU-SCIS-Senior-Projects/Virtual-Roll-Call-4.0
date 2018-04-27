<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$desc = $request->desc;
$address = $request->address;
$lat = $request->lat;
$long = $request->long;
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
$createdby = $request->createdby;
date_default_timezone_set('America/New_York');
$addDate= date('Y-m-d');
$connection = new DBHandler();
$result = $connection->addWatchOrder($desc, $address, $lat, $long, $addDate, $expDate, $startDate, $startTime, $expTime, $zone, $businessName,                                 $ownerName, $woRequester, $phone, $woInstruction, $eName, $eAddress, $ePhone, $createdby);
//convert the response to a json object
die(json_encode($result));
