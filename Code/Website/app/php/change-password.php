<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$id = $request->id;
$currentPassword = $request->current;
$newPassword = $request->new;
$connection = new DBHandler();
$result = $connection->changePassword($id, $currentPassword, $newPassword);
die(json_encode($result));