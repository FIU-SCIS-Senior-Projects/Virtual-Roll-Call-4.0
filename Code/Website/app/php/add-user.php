<?php
require_once('DBHandler.php');
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);
$first_name = $request->fName;
$last_name = $request->lName;
$email = $request->email;
$password = $request->password;
$role = $request->role;
$connection = new DBHandler();
$result = $connection->addUser($first_name, $last_name, $email, $password, $role);
//convert the response to a json object
die(json_encode($result));