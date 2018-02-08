<?php 
require_once('DBHandler.php');
$connection = new DBHandler();
$result = $connection->getlogs();
die(json_encode($result));
