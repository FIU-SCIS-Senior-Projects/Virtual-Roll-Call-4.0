<?php
if ( isset($_POST) && !empty($_POST))
{
	require_once('DBHandler.php');
	print_r($_POST);

	$answers = json_decode( $_POST["answers"] );
	$IDs = json_decode( $_POST["ids"] ); 
	$count = 0; //total number of questions
	$correct = 0; //number of correct answers
	$officer_answers = [];
	for( $x = 0; $x < sizeof($answers); $x++ )
	{
		$count++;
		$order = "qa_" . ($x+1);
		if ( isset($_POST[$order]) )
		{
			$officer_answers[$x] = $_POST[$order];
			if ( $officer_answers[$x] == $answers[$x])
				$correct++;
		}
		else
			$officer_answers[$x] = "";
 	}
	$score = $correct / ( $count == 0 ? 1 : $count ) ;

	$connection = new DBHandler();
	$result = $connection->logQuiz( $IDs[0], $IDs[1], $IDs[2], 
									json_encode($officer_answers), $score, $IDs[3] );
	unset($_POST);
	unset($answers);
	unset($IDs);
	die(json_encode($result));
}