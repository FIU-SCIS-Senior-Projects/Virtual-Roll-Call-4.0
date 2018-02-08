<?php
require_once('DBHandler.php');

$messageBy = $_POST['message_by'];
$message = $_POST['freeMsg'];
$title = $_POST['freeTitle'];
$pinned = 0;
if ( $_POST['pinned_message'] == "on" )
	$pinned = 1;
$description = $_POST['message_description'];
print_r($_POST);
var_dump($_POST);
if ( $message && $title )
{
	$connection = new DBHandler();
	$result = $connection->addMessage( $title, $description, $message, $messageBy, $pinned );
	unset( $_POST );
	$message = null;
	$title = null;
	die(json_encode($result));
}