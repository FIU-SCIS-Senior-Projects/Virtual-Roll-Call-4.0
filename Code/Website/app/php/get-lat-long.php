<?php
require_once('DBHandler.php');
$connection = new DBHandler();
$result = $connection->getLatLong();
die(json_encode($result));