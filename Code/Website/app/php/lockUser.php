<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$id = $request->userid;
$connection = new DBHandler();
$result = $connection->lockUser( $id );
die(json_encode($result));