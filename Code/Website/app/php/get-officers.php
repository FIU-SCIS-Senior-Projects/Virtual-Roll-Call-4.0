<?php
require_once('DBHandler.php');
$connection = new DBHandler();
$result = $connection->getOfficers();
die(json_encode($result));