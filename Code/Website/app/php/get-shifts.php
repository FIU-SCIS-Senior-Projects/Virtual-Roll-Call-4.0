<?php
require_once('DBHandler.php');
$connection = new DBHandler();
$result = $connection->getShifts();
die(json_encode($result));