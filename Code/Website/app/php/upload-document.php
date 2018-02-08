<?php
require_once('DBHandler.php');
print_r($_POST);
$document_name = $_FILES['document']['name'];
$tmp_doc_name = $_FILES['document']['tmp_name'];

$new_name = $_POST['document_name'];
$document_description = $_POST['document_description'] == '' ? $new_name : $_POST['document_description'];
$doc_extension = pathinfo($document_name,PATHINFO_EXTENSION);
$category_id = $_POST['category_id'];
$uploaded_by = $_POST['uploaded_by'];
$pinned = (isset($_POST['pinned']) && $_POST['pinned'] == '1') ? 1 : 0;

date_default_timezone_set('America/New_York');
$upload_date = date('Y-m-d');

$ds = DIRECTORY_SEPARATOR;
$target_path = __DIR__.$ds.'uploads'.$ds.$document_name;

echo $tmp_doc_name;
echo "\n";
echo $target_path;
echo "\n";
echo  $_FILES['document']['error'];

echo move_uploaded_file($tmp_doc_name, $target_path) ? " This should mean that the file was upload "
													 : "This will mean that the file could not be able to move to the place ---------- ";
echo file_exists($target_path) ? "file Exists" : "File does not exist";

$connection = new DBHandler();
$questionaires = array();
$counter = 0;

for ( $x = 1; $x < 5; $x++)
{
	if ( !empty($_POST['question_'.$x]) )
	{
		$qa['q_order'] = "qa_" . $x;
		$qa['question'] = $_POST['question_'.$x];
		$qa['answer_1'] = $_POST['answer_'.$x.'1'];
		$qa['answer_2'] = $_POST['answer_'.$x.'2'];
		$qa['answer_3'] = $_POST['answer_'.$x.'3'];
		$qa['answer_4'] = $_POST['answer_'.$x.'4'];
		$qa['correct_answer'] = $_POST['answer_'.$x.'1'];
		$questionaires[$counter++] = $qa;
	}
}

echo json_encode($questionaires);
$result = $connection->addDocument($new_name, $category_id, $upload_date, $pinned, $uploaded_by, $document_name, $document_description, $questionaires);
//convert the response to a json object
die(json_encode($result));
//!!!!!IMPORTANT CHANGE TO RELEVANT URL !!!!!!
//header("Location: http://localhost:8888/Virtual-Roll-Call-Ver-1.0/app/php/supervisor-profile.php#/upload",TRUE,303);