<?php
require_once('DBHandler.php');
$connection = new DBHandler();
$result = $connection->removeWatchOrders();
//convert the response to a json object
die(json_encode($result));
