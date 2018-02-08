<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$id = $request->lock_id;
$count = $request->lock_count;
$found = $request->lock_found;
$connection = new DBHandler();
$result = $connection->updateFailedLog($found, $id, $count);
die(json_encode($result));