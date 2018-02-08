<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$id = $request->id;
$officer_id = $request->officerId;
$title = $request->title;
$message = $request->message;
$description = $request->description;
$connection = new DBHandler();
$result = $connection->updateMessage($id, $officer_id, $title, $message, $description);
//convert the response to a json object

die(json_encode($result));