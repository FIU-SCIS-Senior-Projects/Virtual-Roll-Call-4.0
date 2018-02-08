<?php
session_start();
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$type = $request->type;
$user_id = $request->user_id;
$doc_category = $request->cat;
$connection = new DBHandler();
$result = $connection->getDocuments($type, $user_id, $doc_category);
$_SESSION["active"] = $user_id;
die(json_encode($result));