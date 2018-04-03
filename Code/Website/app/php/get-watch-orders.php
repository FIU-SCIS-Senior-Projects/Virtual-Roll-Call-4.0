<?php
session_start();
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

$connection = new DBHandler();
if (empty($request)) {	
	$result = $connection->getWatchOrders();
}
else {			
	$user_id = $request->user_id;
	$result = $connection->getWatchOrdersForUser($user_id);	
}
die(json_encode($result));