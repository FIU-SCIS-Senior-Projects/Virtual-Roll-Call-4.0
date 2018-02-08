<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$documentId = $request->id;
$connection = new DBHandler();
$result = $connection->removeDocument($documentId);
//convert the response to a json object

die(json_encode($result));
