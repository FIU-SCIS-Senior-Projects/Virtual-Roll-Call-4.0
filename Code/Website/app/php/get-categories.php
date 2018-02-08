<?php
require_once('DBHandler.php');
$connection = new DBHandler();
$result = $connection->getCategories();
die(json_encode($result));