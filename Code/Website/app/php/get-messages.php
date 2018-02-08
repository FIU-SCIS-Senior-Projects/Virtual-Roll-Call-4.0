<?php
session_start();
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$id = $request->id;
$connection = new DBHandler();
$result = $connection->getMessages( $id );

die(json_encode($result));