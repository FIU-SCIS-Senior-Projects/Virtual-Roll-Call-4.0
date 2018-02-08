<?php
require_once('encryption.php');

class DBHandler
{
	function __construct()
	{
		global $db_connection;
		global $crypter;
		global $stmt;

		$un = 'vrc';
		$pw = 'VirtualRollCall';
		$dbName = 'virtual_roll_call';
		$address = 'localhost';
		$db_connection = new mysqli($address, $un, $pw, $dbName);
		$crypter = new JCrypter();

		if ($db_connection->connect_errno > 0)
			die('Unable to connect to database['.$db_connection->connect_error.']');
	}

	/*******************
			ADDERS FUNCTIONS
			******************/


	/*******************
			GETTERS FUNCTIONS
			******************/
	//
	//Get All Documents from DB
	function getDocuments($type, $id, $category)
	{
		return (strtolower($category) == 'free text')
			? $this->getMessages( $id )  : $this->getMediaDocs($id, $type, $category);
	}

	function getMediaDocs($user_id, $type, $category)
	{
		global $db_connection;
		$documents = [];
		$where_clause = '';

		if ( $type == 'archived' )
			$where_clause = " WHERE ((d.Upload_Date < (DATE(NOW()) - INTERVAL 7 DAY) AND d.Pinned = false) OR d.Manual_Archived = true) " ;
		else if ( $type == 'active' )
			$where_clause = " WHERE ((d.Upload_Date >= (DATE(NOW()) - INTERVAL 7 DAY) OR d.Pinned = true) AND d.Manual_Archived = false) ";

		$sql = "SELECT
					d.document_id, d.document_name, d.category_id, d.upload_date,  d.pinned, d.uploaded_by, c.category_name, d.upload_name, d.description,
					IFNULL(ds.Description, 'Pending') as status,
					IF( ((d.upload_date < (DATE(now()) - INTERVAL 7 DAY) AND d.Pinned = false) OR d.manual_archived = true), 'Yes', 'No') AS archived,
					d.has_quiz,
					IFNULL(q.QA, '') AS questions,
					IFNULL(ql.answers, '') AS answers,
					IFNULL(ql.score, 0) AS score
				FROM documents d
				LEFT JOIN categories c ON d.category_id = c.category_id
    			LEFT JOIN quizzes q ON d.document_name = q.document_name
    			LEFT JOIN ( select answers, score, document_id from quiz_logs where officer_id = ? ) AS ql on ql.document_id = d.document_id
    			LEFT JOIN ( select max(z.statusid) as statusid, z.DocumentId from user_doc_status z where z.OfficerId = ? group by z.documentid ) AS s on s.documentid = d.document_id
    			LEFT JOIN document_status ds ON s.statusid = ds.id "
    		 . $where_clause
    		 . " AND c.category_name = ? "
			 . " ORDER BY d.upload_date DESC";

		$stmt = $db_connection->prepare( $sql );
		$stmt->bind_param('iis', $user_id, $user_id, $category);
		$stmt->execute();
		$stmt->bind_result($id, $name, $cat_id, $created_at, $pinned, $uploaded_by, $cat_name,
						   $upload_name, $doc_description, $status, $archived, $quiz, $qa, $answers, $score);
		while($stmt->fetch())
		{
			$tmp = ["id" => $id,
					"name" => $name,
					"catid" => $cat_id,
					"category" => $cat_name,
					"doc_description" => $doc_description,
					"status" => $status,
					"date" => $created_at,
					"pinned" => $pinned,
					"uploadedBy" => $uploaded_by,
					"upload_name" => $upload_name,
					"archived" => $archived,
					"quiz" => $quiz,
					"qa" => $qa,
					"answers" => $answers,
					"score" => $score];
			array_push($documents, $tmp);
		}

		$stmt->close();
		$db_connection->close();
		return $documents;
	}

	function getUser( $username )
	{
	    global $db_connection;
	    $result = ['userID' => NULL, 'First_Name' => NULL, 'Last_Name' => NULL, 'Username' => NULL, 'Role' => NULL];
	    $sql = 'SELECT userID, First_Name, Last_Name, Username, Role FROM OFFICERS WHERE Username=?';
	    $stmt = $db_connection->prepare($sql);
	    $stmt->bind_param('s', $username);
	    $stmt->execute();
	    $stmt->bind_result($result['userID'], $result['First_Name'], $result['Last_Name'], $result['Username'], $result['Role']);
	    if (!$stmt->fetch()) { return $result; }

		$stmt->close();
		$db_connection->close();
	    return $result;
	}

	function getTally( $username )
	{
		global $db_connection;
		$result = ['userid' => 0, 'count' => 0, 'locked' => 0, 'created' => NULL];

		$query = "SELECT userID, lock_count, lock_status, l.created_at
				  FROM OFFICERS O LEFT JOIN LOGIN_LOGS L ON O.userID = L.log_id
				  WHERE lower(O.username) = lower(?) ";

		$stmt = $db_connection->prepare($query);
	    $stmt->bind_param('s', $username);
	    $stmt->execute();
	    $stmt->bind_result( $result['userid'],$result['count'],$result['locked'],$result['created'] );

	    if (!$stmt->fetch()) return $result;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	function getOfficers()
	{
		global $db_connection;
		$officers = [];
		$stmt = $db_connection->prepare( 'SELECT userID, First_Name, Last_Name, Username, Role FROM OFFICERS' );
		$stmt->execute();
		$stmt->bind_result( $userID, $First_Name, $Last_Name, $Username, $Role );
		while($stmt->fetch())
		{
			$tmp = ["id" => $userID,
					"firstName" => $First_Name, "lastName" => $Last_Name,
					"username" => $Username, "role" => $Role];
			array_push($officers, $tmp);
		}
		$stmt->close();
		$db_connection->close();
		return $officers;
	}

	/*******************
			HELPER FUNCTIONS
			******************/
	//
	function GetStatusDescription($statusId)
	{
		global $db_connection;
		$result = [];
		$statusDescription = 'Not Defined';

		$sql = "SELECT Description FROM DOCUMENT_STATUS WHERE Id=?";
		$stmt = $db_connection->prepare($sql);

		if(!$stmt->bind_param('i',$statusId))
			if ($stmt->execute()){
				$stmt->bind_result($statusDescription);
				while($stmt->fetch()){ $statusDescription = $statusDescription; };
				$stmt->close();
		}
		$db_connection->close();
		return $statusDescription;
	}

	function GetStatusArray()
	{
		global $db_connection;
		$result = [];

		$sql = "SELECT Id, Description FROM DOCUMENT_STATUS ORDER BY Id";
		$stmt = $db_connection->prepare($sql);

		if ($stmt->execute())
		{
			$stmt->bind_result($id,$statusDescription);
			array_push($result, "Not Defined");
			while($stmt->fetch())
					array_push($result, $statusDescription);
			$stmt->close();
		}

		return $result;
	}

	//ADD NEW WATCH ORDER TO DATABASE
	function addWatchOrder($desc, $address, $lat, $long, $addDate, $expDate) {
		global $db_connection;
		$result = ['Added' => false];
		$sql = "INSERT INTO WATCH_ORDERS (`Desc`,`Address`,`Lat`,`Lng`,`AddDate`,`ExpDate`) VALUES (?,?,?,?,?,?)";
		$stmt = $db_connection->prepare($sql);
		if (!$stmt->bind_param('ssddss', $desc, $address, $lat, $long, $addDate, $expDate))
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		if (!$stmt->execute())
		{
			return $result;
		}
			$result['Added'] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}
	function removeWatchOrders() {
		global $db_connection;
		$result = ['RemovedAll' => false];
		$sql = "DELETE FROM WATCH_ORDERS";
		$stmt = $db_connection->prepare($sql);
		if (!$stmt->execute())
		{
			return $result;
		}
		$result['RemovedAll'] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}
	function removeWatchOrder($id) {
		global $db_connection;
		$result = ["Removed" => false];
		$table = "WATCH_ORDERS";
		$sql = "DELETE FROM $table
		        WHERE `Id`=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('d', $id) )
		{
			return $result;
		}
		if (!$stmt->execute())
		{
			return $result;
		}
		$result["Removed"] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}
	function getWatchOrders() {
		global $db_connection;
		$orders = [];
		$sql = "SELECT `Id`,`Desc`,`Address`,`Lat`,`Lng`,`AddDate`,`ExpDate` FROM WATCH_ORDERS";
		$stmt = $db_connection->prepare($sql);
		$stmt->execute();
		$stmt->bind_result($Id, $Desc, $Address, $Lat, $Lng, $AddDate, $ExpDate);
		while($stmt->fetch()){
			$tmp = ["Id" => $Id,
			"Desc" => $Desc,
			"Address" => $Address,
			"Lat" => $Lat,
			"Lng" => $Lng,
			"AddDate" => $AddDate,
			"ExpDate" => $ExpDate];
			array_push($orders, $tmp);
		}
		$stmt->close();
		$db_connection->close();
		return $orders;
	}
	function editWatchOrder($id, $desc, $address, $lat, $lng, $expDate) {
		global $db_connection;
		$result = ["Updated" => false];
		$table = "WATCH_ORDERS";
		$sql = "UPDATE $table SET `Desc`=?, `Address`=?, `Lat`=?, `Lng`=?, `ExpDate`=? WHERE `Id`=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('sssssd', $desc, $address, $lat, $lng, $expDate, $id) )
		{
			return $result;
		}
		if (!$stmt->execute())
		{
			return $result;
		}
		$result["Updated"] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

  //ADD NEW USER TO DATABASE
	function addUser($first_name, $last_name, $username, $password, $role) {
		global $db_connection;
		global $crypter;
		$hash_password = $crypter->hash($password);

		$result = ['Added' => false,'Username' => $username, 'Password' => $hash_password];
		$sql = "INSERT INTO OFFICERS (First_Name, Last_Name, Username, Password, Role) VALUES (?,?,?,?,?)";
		$stmt = $db_connection->prepare($sql);
		if (!$stmt->bind_param('sssss', $first_name, $last_name, $username, $hash_password, $role))
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		if (!$stmt->execute())
			return $result;

    	$result['Added'] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	function editUser($id, $first_name, $last_name, $username, $role) {
		global $db_connection;
		$result = ["Updated" => false];
		$table = "OFFICERS";
		$sql = "UPDATE $table SET First_Name=?, Last_Name=?, Username=?, Role=?
		        WHERE userID=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('ssssd', $first_name, $last_name, $username, $role, $id) )
		{
			return $result;
		}
		if (!$stmt->execute())
		{
			return $result;
		}
		$result["Updated"] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	function removeUser($id) {
		global $db_connection;
		$result = ["Removed" => false];
		$table = "OFFICERS";
		$sql = "DELETE FROM $table
		        WHERE userID=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('d', $id) )
		{
			return $result;
		}
		if (!$stmt->execute())
		{
			return $result;
		}
		$result["Removed"] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	function loginUser($username, $password){
		global $db_connection;
		global $crypter;

        //store the result here
		$result = ['userID' => NULL,
				   'First_Name' => NULL, 'Last_Name' => NULL,
				   'Username' => NULL, 'Password' => NULL, 'Role' => NULL,
				   'Lock_Count' => NULL];
		$failed = $result;

		$sql = 'SELECT userID, First_Name, Last_Name, Username, Password, Role, lock_count
				FROM OFFICERS o
				LEFT JOIN LOGIN_LOGS l ON o.userID = l.log_id
				WHERE lower(Username) = lower(?) AND Active = 1';
		$stmt = $db_connection->prepare($sql);
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$stmt->bind_result(	$result['userID'],
							$result['First_Name'], $result['Last_Name'],
						   	$result['Username'], $result['Password'],
						   	$result['Role'], $result['Lock_Count'] );

		if (!$stmt->fetch()) return $failed;
		if ( !$crypter->verify($password, $result['Password'])) return $failed;

		$stmt->close();
		$db_connection->close();
		return $result;
	}

	function changePassword($id, $curr_pw, $new_pw){
		global $db_connection;
		global $crypter;
		$hash_new_pw = $crypter->hash($new_pw);

		$result = ['userID' => NULL, 'Updated' => NULL];

		$stmt = $db_connection->prepare('UPDATE OFFICERS SET Password=? WHERE UserID=?');
		$stmt->bind_param('sd', $hash_new_pw, $id);
		$stmt->execute();
		if ($stmt->affected_rows === 1)
			$result['Updated'] = true;

		$stmt->close();
		$db_connection->close();
		return $result;
	}

	function updateFailedLog( $lock_found, $log_id, $lock_count )
	{
		global $db_connection;
		$result = ['status' => ''];
		$lockStatus = 0;

		if ( $lock_found )
		{
			$query =  'UPDATE login_logs SET lock_count = ?, updated_at = now() ';
			if ( $lock_count == 1)
				$query .= ', created_at = now() ';
			$query .= 'WHERE log_id = ? ';
			$stmt = $db_connection->prepare($query);
			if ( !$stmt->bind_param('ii', $lock_count, $log_id ) )
				$result["status"] = "Query failed at biding. ";
		}
		else
		{
			$stmt = $db_connection
					->prepare('INSERT INTO login_logs (log_id, created_at, lock_count, lock_status )
							   VALUES (?,now(),?,?) ');
			if ( !$stmt->bind_param('iii', $log_id, $lock_count, $lockStatus) )
				$result["status"] = "Query failed at biding. ";
			else
				$result["status"] = "Failed Attempt Recorded.";
		}

		if (!$stmt->execute()) return $result;
		$stmt->close();
		$db_connection->close();

		return $result;
	}

	function lockUser( $id )
	{
		global $db_connection;
		$result = ['status' => ''];
		$query = "UPDATE login_logs SET lock_status = 1 WHERE log_id = ?";
		$stmt = $db_connection->prepare($query);
		if( !$stmt->bind_param('i', $id) ) return $result;
		if (!$stmt->execute())  return $result;

		$query = 'UPDATE officers SET Active = 0 WHERE userID = ?';
		$stmt = $db_connection->prepare($query);
		if( !$stmt->bind_param('i', $id) ) return $result;
		if (!$stmt->execute())  return $result;

		$result['status'] = 'Your account has been locked!';
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	function resetLock ( $id )
	{
		global $db_connection;
		$result = ['reset' => false];
		$logCount = 0;
		$stmt = $db_connection->
			prepare('UPDATE LOGIN_LOGS SET lock_count=?, created_at=now() WHERE log_id=?');
		if(!$stmt->bind_param('ii', $logCount, $id)) return $result;
		if(!$stmt->execute()) return $result;
		$result['reset'] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	//GET ALL MESSAGES FROM THE DATABASE
	function getMessages( $user_id )
	{
		global $db_connection;

		//Getting Category ID for Free Text messages
		$cat_name = 'free text';
		$query = "SELECT category_id FROM Categories WHERE lcase(category_name) = ? ";
				$stmtSelect = $db_connection->prepare($query);
		if ( !$stmtSelect->bind_param('s', $cat_name) )
			echo "Binding parameters failed: (" . $stmtSelect->errno . ") " . $stmtSelect->error;;
		if (!$stmtSelect->execute())
			return "Error updating entry on USER_DOC_STATUS";
		else
		{
			$stmtSelect->bind_result($result);
			while($stmtSelect->fetch())
				$cat_id = $result;
			$stmtSelect->close();
		}

		$messages = [];
		$query = "SELECT MessageId, Pinned, Title, m.Description, Message, Created_by,
						 DATE_FORMAT(Created_at, '%c/%d/%y'), Updated_by, Updated_at,
						 IF(
							((Created_at < (DATE(now()) - INTERVAL 7 DAY) AND Pinned = false)
							  	OR Manual_Archived = true),
						'Yes', 'No') AS archived,
						( SELECT ds.Description
						  FROM Document_Status ds
						  WHERE ds.id = ( SELECT IFNULL(max(statusid),1)
										  FROM user_doc_status uds
										  WHERE uds.documentid = m.MessageId
										  AND uds.officerId = ? ) ) as status
				   FROM Messages m";
		$stmt = $db_connection->prepare($query);
		$stmt->bind_param('i', $user_id);

		if (!$stmt->execute())
			return "Execute Statement failed: (" . $stmt->errno . ") " . $stmt->error;
		$stmt->bind_result( $message_id, $pinned, $title, $description, $message, $createdBy,
							$createdAt, $updatedBy, $updatedAt, $archived, $status);
		while($stmt->fetch())
		{
			$tmp = ["id" => $message_id,
					"pinned" => $pinned,
					"name" => $title,
					"msg_description" => $description,
					"message" => $message,
					"catid" => $cat_id,
					"uploadedBy" => $createdBy,
					"date" => $createdAt,
					"updated_by" => $updatedBy,
					"archived" => $archived,
					"status" => $status,
					"updated_at" => $updatedAt];
			array_push($messages, $tmp);
		}
		$stmt->close();
		$db_connection->close();
		return $messages;
	}

	function getQuiz( $quiz_doc )
	{
		global $db_connection;

		$tmp;
		$query = "SELECT QA FROM Quizzes WHERE Document_Name = ?";
		$stmt = $db_connection->prepare($query);
		$stmt->bind_param('s', $quiz_doc);
		if (!$stmt->execute())
			return "Execute Statement failed: (" . $stmt->errno . ") " . $stmt->error;
		$stmt->bind_result( $qa );
		while( $stmt->fetch() ) {
			$tmp = ["questions_answers" => $qa];
		}

		$stmt->close();
		$db_connection->close();
		return $tmp;
	}

		//GET ALL MESSAGES FROM THE DATABASE
	function searchMessage( $msg_id )
	{
		global $db_connection;
		$tmp;
		$query = "SELECT MessageId, Pinned, Title, m.Description, Message,
						 Created_by, Created_at, Updated_by, Updated_at, CONCAT(First_Name, ' ', Last_Name)
				   FROM Messages m, Officers o
				   WHERE m.created_by = o.userid
				   AND m.MessageId = ?";
		$stmt = $db_connection->prepare($query);
		$stmt->bind_param('i', $msg_id);
		if (!$stmt->execute())
			return "Execute Statement failed: (" . $stmt->errno . ") " . $stmt->error;//return $result;
		$stmt->bind_result( $message_id, $pinned, $title, $description, $message,
							$createdBy, $createdAt, $updatedBy, $updatedAt, $officer);
		while($stmt->fetch()){
			$tmp = ["id" => $message_id,
					"pinned" => $pinned,
					"name" => $title,
					"doc_description" => $description,
					"message" => $message,
					"uploadedBy" => $createdBy,
					"date" => $createdAt,
					"updated_by" => $updatedBy,
					"officer" => $officer,
					"updated_at" => $updatedAt];
		}
		$stmt->close();
		$db_connection->close();
		return $tmp;
	}

    function getlogs()
    {
		$statusArray = $this->GetStatusArray();

        global $db_connection;
        $logs = [];
        $sql = 'SELECT
				OFFICERS.First_Name,
				OFFICERS.Last_Name,
				DOCUMENTS.Document_Name,
				LOGS.DOC,
				DOCUMENTS.Upload_Date AS Uploaded,
				USER_DOC_STATUS.StartDateTime AS Started,
				USER_DOC_STATUS.EndDateTime AS Completed,
				CONCAT(TRUNCATE((TIMESTAMPDIFF(SECOND, USER_DOC_STATUS.startdatetime, USER_DOC_STATUS.enddatetime)), 2), \' Sec\') AS Duration,
				USER_DOC_STATUS.StatusId
				FROM DOCUMENTS
				LEFT JOIN LOGS ON LOGS.documentid = DOCUMENTS.document_ID
				LEFT JOIN OFFICERS ON LOGS.userid = OFFICERS.userID
				LEFT JOIN USER_DOC_STATUS ON DOCUMENTS.document_ID = USER_DOC_STATUS.DocumentId AND OFFICERS.userID = USER_DOC_STATUS.OfficerId
				LEFT JOIN DOCUMENT_STATUS ON USER_DOC_STATUS.StatusId = DOCUMENT_STATUS.Id
				ORDER BY DOCUMENTS.Upload_Date DESC';

            $stmt = $db_connection->prepare($sql);
            $stmt->execute();
            $stmt->bind_result( $First_Name, $Last_Name, $Document_Name, $DOC,
								$Uploaded, $Started, $Completed, $Duration, $Status );
            while($stmt->fetch()){
                    $tmp = [
						"Full_Name" => $First_Name.' '.$Last_Name,
						"Document_Name" => $Document_Name,
						"DOC" => $DOC,
						"Uploaded" => $Uploaded,
						"Started" => $Started,
						"Completed" => $Completed,
						"Duration" => $Duration < 0 ? '0.00 Sec' : $Duration,
						"Status" => $Status == NULL ? $statusArray[1] : $statusArray[(int)$Status]
					];
                    array_push($logs, $tmp);
            }
            $stmt->close();
            $db_connection->close();
            return $logs;
        }

	//TODO: UPLOAD DATE COME WITH 1 DAY MORE THAN THE ACTUAL DATE
	//ADD DOCUMENT METADATA  TO THE DATABASE
	function addDocument($document_name, $category, $upload_date, $pinned, $uploaded_by, $upload_name, $upload_description, $questions){
		global $db_connection;
		$result = ['Added' => false];
		$archived = 0;
		$hasQuestion = !empty($questions);
		$sql = 'INSERT INTO
						DOCUMENTS (Document_Name, Category_ID, Upload_Date, Pinned, Uploaded_By, Upload_Name, Description, Manual_Archived, Has_Quiz)
						VALUES 	  (?,?,?,?,?,?,?,?,?)';
		$stmt = $db_connection->prepare($sql);

		if (!$stmt->bind_param('sdsdsssii', $document_name, $category, $upload_date, $pinned, $uploaded_by, $upload_name, $upload_description, $archived, $hasQuestion))
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		if (!$stmt->execute())
			return "Execute Statement failed: (" . $stmt->errno . ") " . $stmt->error;

		$result['Added'] = true;

		if ( $hasQuestion )
		{
			$questionaire = json_encode($questions);
			$sql = "INSERT INTO Quizzes (document_name, QA) VALUES (?, ?)";
			$stmt = $db_connection->prepare($sql);
			if ( !$stmt->bind_param('ss', $document_name, $questionaire) )
				echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			if (!$stmt->execute())
				return "Execute Statement failed: (" . $stmt->errno . ") " . $stmt->error;

			$result['Quizz'] = true;
		}
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	function addMessage($title, $description, $new_msg, $uploaded_by, $pin) {
		global $db_connection;
		$result = ['Added' => false];
		$query = 'INSERT INTO Messages (Created_by, Title, Description, Message, Created_at, Pinned)
				  VALUES(?,?,?,?,now(),?)';
		$stmt = $db_connection->prepare($query);

		if( !$stmt->bind_param('isssi', $uploaded_by, $title, $description, $new_msg, $pin) )
			$result['Added'] = "Unable to add parameters";
		if (!$stmt->execute())
			return "Execute Statement failed: (" . $stmt->errno . ") " . $stmt->error;

		$result['Added'] = true;
		$stmt->close();
		return $result;
	}

    //GET ALL CATEGORIES FROM THE DATABASE
	function getCategories(){
		global $db_connection;
		$categories = [];
		$sql = "SELECT category_ID, Category_Name FROM CATEGORIES";
		$stmt = $db_connection->prepare($sql);
		$stmt->execute();
		$stmt->bind_result($id, $name);
		while($stmt->fetch())
			array_push($categories, ["id" => $id, "name" => $name]);
		$stmt->close();
		$db_connection->close();
		return $categories;
	}

	//GET PENDING DOCS BY CATEGORY
	function getPendingDocs( $user_id )
	{
		global $db_connection;
		$pendings = [];

		//Count Documents
		$query = "SELECT count(1), category_name
				  FROM documents d JOIN categories c ON c.category_ID = d.Category_ID
 				  WHERE document_id NOT IN
							(select documentId from user_doc_status uds
						     where uds.DocumentId = d.document_ID and officerId = ? )
				  AND ((d.Upload_Date >= (DATE(NOW()) - INTERVAL 7 DAY) OR d.Pinned = true) AND d.Manual_Archived = false)
				  GROUP BY category_name
                  UNION
                  SELECT count(1), 'Free Text'
			  	  FROM Messages m
				  WHERE MessageId NOT IN
				 (select documentId from user_doc_status uds
				  where uds.DocumentId = m.MessageId and officerId = ? )
				  AND ((m.created_at >= (DATE(NOW()) - INTERVAL 7 DAY) OR m.Pinned = true) AND m.Manual_Archived = false)";
		$stmt = $db_connection->prepare($query);
		if ( !$stmt->bind_param('ii', $user_id, $user_id) )
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		if ( !$stmt->execute() ) return $result;
		$stmt->bind_result($count, $category);
		while ( $stmt->fetch()) {
			$tmp = ['category' => $category, 'pending' => $count];
			array_push($pendings, $tmp);
		}
		$stmt->close();

		$db_connection->close();
		return $pendings;
	}

	//ADD NEW CATEGORY TO DATABASE
	function addCategory($name) {
		global $db_connection;
		$result = ['Added' => false,'name' => $name];
		$sql = "INSERT INTO CATEGORIES (Category_Name) VALUES (?)";
		$stmt = $db_connection->prepare($sql);
		if (!$stmt->bind_param('s', $name)){
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (!$stmt->execute()){
			return $result;
		}
        $result['Added'] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	//REMOVE CATEGORY FROM THE DATABASE
	function removeCategory($cat_id) {
		global $db_connection;
		$result = ["Removed" => false];
		$table = "CATEGORIES";
		$sql = "DELETE FROM $table WHERE category_ID = ?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('d', $cat_id) )
			return $result;

		if (!$stmt->execute())
			return $result;

		$result["Removed"] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	//REMOVE DOCUMENT
	function removeDocument( $document_id )
	{
			global $db_connection;
			$result = ["Document_Removed" => false];
			$query = 'DELETE FROM Documents WHERE Document_ID = ?';
			$stmt = $db_connection->prepare($query);

			if( !$stmt->bind_param('i', $document_id) )
				return $result;
			if (!$stmt->execute())
				return $result;

			$result["Document_Removed"] = true;
			$stmt->close();
			$db_connection->close();
			return $result;
	}

	//REMOVE MESSAGE FROM THE DATABASE
	function deleteMessage( $message_id )
	{
		global $db_connection;
		$result = ["Removed" => false];
		$query = 'DELETE FROM Messages WHERE MessageId = ?';
		$stmt = $db_connection->prepare($query);

		if( !$stmt->bind_param('i', $message_id) )
			return $result;
		if (!$stmt->execute())
			return $result;

		$result["Removed"] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

    //UPDATE CATEGORY IN THE DATABASE
	function updateCategory($cat_id, $cat_name) {
		global $db_connection;
		$result = ["Updated" => false];
		$sql = "UPDATE CATEGORIES SET Category_Name=? WHERE category_ID=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('sd', $cat_name, $cat_id)){
			return $result;
		}
		if (!$stmt->execute()){
			return $result;
		}
		$result["Updated"] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	//UPDATE MESSAGES
	function updateMessage($id, $officerId, $title, $message, $description) {
		global $db_connection;
		$result = ["Updated" => false];
		$sql = 'UPDATE Messages
				SET Title = ?, Message = ?, Updated_by = ?, Description = ?, Updated_at = now()
				WHERE MessageId = ?';
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('ssisi', $title, $message, $officerId, $description, $id))
			return "Bind parameters error";

		if (!$stmt->execute())
			return $result;

		$result["Updated"] = true;
		$stmt->close();
		$db_connection->close();
	}


    //RESET OFFICER PASSWORD IN THE DATABASE
	function resetPassword($id, $reset_pw){
		global $db_connection;
		global $crypter;

		$hash_reset = $crypter->hash($reset_pw);
		$result = ['userID' => $id, 'Updated' => false];
		$active = 1;

		//Update the Officers Relation
		$stmt = $db_connection->prepare('UPDATE OFFICERS SET Password=?, Active=? WHERE UserID=?');
		if(!$stmt->bind_param('sid', $hash_reset, $active, $id)) return $result;
		if(!$stmt->execute()) return $result;

		//Update the Login_logs Relation
		$logCount = 0; $lockStatus = 0;
		$stmt = $db_connection->prepare('UPDATE LOGIN_LOGS SET lock_count=?, lock_status=? WHERE log_id=?');
		if(!$stmt->bind_param('iii', $logCount, $lockStatus, $id)) return $result;
		if(!$stmt->execute()) return $result;

		$result["Updated"] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	//GET ALL CATEGORIES FROM THE DATABASE
	function getSiteNames(){
		global $db_connection;
		$result = [];
		$sql = 'SELECT Application_Name, Department_Name FROM SETTINGS';
		$stmt = $db_connection->prepare($sql);
		$stmt->execute();
		$stmt->bind_result($app_name, $dept_name);
		while($stmt->fetch()){
			$result = ["app_name" => $app_name, "dept_name" => $dept_name];
		}
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	//UPDATE CATEGORY IN THE DATABASE
	function updateAppName($app_name) {
		global $db_connection;
		$result = ["Updated" => false];
		$sql = "UPDATE SETTINGS SET Application_Name=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('s', $app_name)){
			return $result;
		}
		if (!$stmt->execute()){
			return $result;
		}
		$result["Updated"] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	//UPDATE CATEGORY IN THE DATABASE
	function updateDeptName($dept_name) {
		global $db_connection;
		$result = ["Updated" => false];
		$sql = "UPDATE SETTINGS SET Department_Name=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('s', $dept_name)) return $result;
		if (!$stmt->execute()) return $result;
		$result["Updated"] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	function logQuiz( $officerId, $documentId, $category_id, $answers, $score, $status )
	{
		global $db_connection;
		$sql = 'INSERT INTO quiz_logs (officer_id, document_id, answers, score) values(?,?,?,?)';
		$stmt = $db_connection->prepare($sql);
		$stmt->bind_param('iisd', $officerId, $documentId, $answers, $score);
		$stmt->execute();
		$result["Quiz_Logged"] = true;

		$this->documentStatusUpdate($officerId, $documentId, $category_id, $status);
		$result["Document_Saved"] = true;
		return $result;
	}

	function updateDocument($id,$name,$categories,$pinned){
		global $db_connection;
		$sql = "Update DOCUMENTS set Document_Name=?,Category_ID=?,Pinned=? where document_ID =?";
		$rs = $db_connection->prepare($sql);
		if(!$rs->bind_param('siii',$name,$categories,$pinned,$id))
				return "Bind paramenter error";

		if(!$rs->execute())
			return "Execute Error";
		$rs->close();
		$db_connection->close();
		return true;
	}

        function deleteArchive($from,$to){
                global $db_connection;
                $officers = [];
                $from = date("Y-m-d",  strtotime($from));
                $to = date("Y-m-d",  strtotime($to));

                $sql = "select Document_name,Uploaded_By,Upload_Name from DOCUMENTS WHERE (Upload_Date BETWEEN ? AND ?) and pinned = 0";
                $rs = $db_connection->prepare($sql);
                if(!$rs->bind_param('ss',$from,$to))
                        return "Bind paramenter error";

                $rs->execute();
                $rs->bind_result($Document_name, $Uploaded_By,$upload_Name);
                while($rs->fetch()){

                    unlink("uploads/".$upload_Name);
                    $tmp = ["name" => $Document_name,
                        "uploaded" => $Uploaded_By,
                        "file" => $upload_Name];
                    array_push($officers, $tmp);
                }
                $sql = "SET SQL_SAFE_UPDATES = 0;";
                $rs = $db_connection->prepare($sql);
                $rs->execute();

                $sql = "delete from DOCUMENTS WHERE (Upload_Date BETWEEN ? AND ?) and pinned = 0";
                $rs = $db_connection->prepare($sql);
                if(!$rs->bind_param('ss',$from,$to))
                        return "Bind paramenter error";
                $rs->execute();
                $rs->close();
                $db_connection->close();
                return $officers;
        }

	//UPDATE DOCUMENT STATUS
	function documentStatusUpdate($user_id, $document_id, $category_id, $status)
	{
		global $db_connection;
		$insert = true;
		$result = [];
		$new_status_id;
		$result = ["id" => $document_id, "status" => 1];

		if ( $status != 'Done' )
			if ( $status == 'Pending' )
				$new_status_id = 2;
			else  { $new_status_id = 3; $insert = false; }

		//document is read by first time, status will be set to reviewed and start date time will be set as well
		if( $insert )
		{
			$sql = "INSERT INTO USER_DOC_STATUS (StartDateTime, EndDateTime, DocumentId,OfficerId,StatusId, CategoryId)
						   values(now(),now(),?,?,?,?) ";
			$stmt = $db_connection->prepare($sql);
			$stmt->bind_param('iiii',$document_id, $user_id, $new_status_id, $category_id);
		}
		else
		{//document has been mark as done, status will be change to done and end date time will be set as well
			//$EndDateTime = getdate();
			$sql = 'UPDATE USER_DOC_STATUS SET StatusId= ?, EndDateTime=now()
				    WHERE DocumentId = ? AND OfficerId = ? AND CategoryId = ?';
			$stmt = $db_connection->prepare($sql);
			if( !$stmt->bind_param('iiii',$new_status_id, $document_id, $user_id, $category_id) )
			{
				$result["error"] = "Error binding parameters on USER_DOC_STATUS";
				return $result;
			}
		}

		if (!$stmt->execute()) {
			$result["error"] = "Error entry on USER_DOC_STATUS";
			return $result;
		}

		$sql = 'SELECT DocumentId, Description
				FROM USER_DOC_STATUS uds
				JOIN DOCUMENT_STATUS ds ON StatusId = ds.Id
				WHERE DocumentId = ? AND OfficerId = ? AND CategoryId = ?';
		$stmtSelect = $db_connection->prepare($sql);

		if ( !$stmtSelect->bind_param('iii', $document_id, $user_id, $category_id) )
			echo "Binding parameters failed: (" . $stmtSelect->errno . ") " . $stmtSelect->error;;

		if (!$stmtSelect->execute()) {
			$result["error"] = "Error updating entry on USER_DOC_STATUS";
			return $result;
		}
		else {
			$stmtSelect->bind_result($document_id, $new_status);
			while($stmtSelect->fetch())
				$result["status"] = $new_status;

			$stmtSelect->close();
			$this->documentSaveLog($user_id, $document_id, $category_id);
			return $result;
		}
		return $result;
	}

	function documentSaveLog($user_id, $document_id, $category_id)
	{
		global $db_connection;
		$sql = "INSERT INTO Logs (DOC, documentid, userid, categoryid) values(now(),?,?,?) ";
		$stmt = $db_connection->prepare($sql);
		$stmt->bind_param('iii',$document_id,$user_id, $category_id);
		$stmt->execute();
		$db_connection->close();
	}
}
