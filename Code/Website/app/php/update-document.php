<?php 
require_once('DBHandler.php');

$postdata = file_get_contents("php://input");
$request = json_decode($postdata);


$id = $request->id;
$name = $request->name;
$categories = $request->categories->id;
$pinned = $request->pinned;

$connection = new DBHandler();
$result = $connection->updateDocument($id,$name,$categories,$pinned);
//convert the response to a json object
die(json_encode($result));
