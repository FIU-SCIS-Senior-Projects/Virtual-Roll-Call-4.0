<?php
require_once('DBHandler.php');
$connection = new DBHandler();
$result = $connection->getSiteNames();
die(json_encode($result));
