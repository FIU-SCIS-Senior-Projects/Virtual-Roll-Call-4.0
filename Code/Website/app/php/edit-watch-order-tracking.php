<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$wo_id = $request->wo_id;
$user_id = $request->user_id;
$is_selected = $request->is_selected;

$connection = new DBHandler();
$result = $connection->editWatchOrderTracking($wo_id, $user_id, $is_selected);
//convert the response to a json object
die(json_encode($result));