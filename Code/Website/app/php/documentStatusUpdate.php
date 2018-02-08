<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$user_id = $request->user_id;
$document_id = $request->document_id;
$category_id = $request->category_id;
$new_status = $request->new_status;
$connection = new DBHandler();
$result = $connection->documentStatusUpdate($user_id, $document_id, $category_id, $new_status);
die(json_encode($result));
