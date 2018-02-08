<?php
require_once('DBHandler.php');
$connection = new DBHandler();
$result = $connection->getWatchOrders();
die(json_encode($result));
