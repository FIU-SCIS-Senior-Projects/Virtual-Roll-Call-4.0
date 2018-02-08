<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$id = $request->id;
$reset_pass = $request->reset_pass;
$connection = new DBHandler();
$result = $connection->resetPassword($id, $reset_pass);
die(json_encode($result));