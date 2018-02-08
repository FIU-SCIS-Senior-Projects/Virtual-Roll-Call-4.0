<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$user_id = $request->user_id;
$connection = new DBHandler();
$result = $connection->getPendingDocs( $user_id );
die(json_encode($result));