<?php
require_once('DBHandler.php');
$connection = new DBHandler();
$result = $connection->getTimeout();
die(json_encode($result));