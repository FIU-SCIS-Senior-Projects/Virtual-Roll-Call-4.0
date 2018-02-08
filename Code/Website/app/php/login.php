<?php
session_start();

require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$username = $request->username;
$password = $request->password;
$connection = new DBHandler();
$result = $connection->loginUser($username, $password);

$_SESSION["officer_role"] = $result["Role"];
die(json_encode($result));