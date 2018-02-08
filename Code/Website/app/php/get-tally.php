<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$username = $request->username;
$connection = new DBHandler();
$result = $connection->getTally($username);
die(json_encode($result));