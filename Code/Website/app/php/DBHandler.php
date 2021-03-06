<?php
require_once('encryption.php');

class DBHandler
{
	function __construct()
	{
		global $db_connection;
		global $crypter;
		global $stmt;

		$un = 'root';
		$pw = '1234';
		$dbName = 'virtual_roll_call';
		$address = 'localhost:3306';
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
		if (strtolower($type) == 'all')
			return $this->getDocumentList();
		else if (strtolower($category) == 'free text')
			return $this->getMessages( $id );
		else
			return $this->getMediaDocs($id, $type, $category);

		// return (strtolower($category) == 'free text')
		// 	? $this->getMessages( $id )  : $this->getMediaDocs($id, $type, $category);
	}


	function getDocumentList(){
		global $db_connection;
		$documents = [];
		$sql = "SELECT d.Document_ID, 
					   d.Document_Name, 
					   c.Category_ID, 
					   c.Category_Name, 
					   d.Upload_Date, 
					   CASE WHEN d.Pinned = 0 THEN 'N' else 'Y' end as 'Pinned', 
					   d.Uploaded_By, CASE WHEN D.Manual_Archived = 0 THEN 'N' ELSE 'Y' END as 'Archived'
				FROM DOCUMENTS d JOIN CATEGORIES c on d.Category_ID = c.Category_ID";
		$stmt = $db_connection->prepare( $sql );
		$stmt->execute();
		$stmt->bind_result($id, $name, $cat_id, $cat_name, $created_at, $pinned, $uploaded, $archived);
		while($stmt->fetch())
		{
			$tmpArray = ["id" => $id,
					"name" => $name,
					"catid" => $cat_id,
					"category" => $cat_name,
					"date" => $created_at,
					"pinned" => $pinned,
					"uploadedBy" => $uploaded,
					"archived" => $archived];
			array_push($documents, $tmpArray);
		}
		$stmt->close();
		$db_connection->close();
		return $documents;
	}

	function getMediaDocs($user_id, $type, $category)
	{
		global $db_connection;
		$documents = [];
		$where_clause = '';

		if ( $type == 'archived' )
			$where_clause = " WHERE s.IsArchived = 1 " ;
		else if ( $type == 'active' )
			// $where_clause = " WHERE (s.IsArchived = 0) or (d.document_id NOT IN (select uds.documentId from user_doc_status uds where uds.DocumentId = d.document_ID and uds.officerId = ?)) ";
			$where_clause = " WHERE ((s.IsArchived = 0) or (d.document_id NOT IN (select uds.documentId 
																				from user_doc_status uds 
																				join categories cat on uds.CategoryId = cat.Category_ID 
																				where (uds.DocumentId = d.document_ID and cat.Category_ID  = d.Category_ID and cat.Category_Name = ? and uds.officerId = ? )))) ";
		$sql = "SELECT
					d.document_id, d.document_name, d.category_id, d.upload_date,  d.pinned, d.uploaded_by, c.category_name, d.upload_name, d.description,
					IFNULL(ds.Description, 'Pending') as status,
					IF((s.IsArchived = 1), 'Yes', 'No') AS archived,
					d.has_quiz,
					IFNULL(q.QA, '') AS questions,
					IFNULL(ql.answers, '') AS answers,
					IFNULL(ql.score, 0) AS score
				FROM documents d
				LEFT JOIN categories c ON d.category_id = c.category_id
    			LEFT JOIN quizzes q ON d.document_name = q.document_name
    			LEFT JOIN ( select answers, score, document_id from quiz_logs where officer_id = ? ) AS ql on ql.document_id = d.document_id
    			 LEFT JOIN ( select z.statusid as statusid, z.DocumentId, z.IsArchived from user_doc_status z where z.id in (select max(z2.id) from user_doc_status z2 where z2.OfficerId = ? group by documentid) ) AS s on s.documentid = d.document_id
    			LEFT JOIN document_status ds ON s.statusid = ds.id "
    		 . $where_clause
    		 . " AND c.category_name = ? "
			 . " ORDER BY d.upload_date DESC";

		$stmt = $db_connection->prepare( $sql );
		if ( $type == 'archived' )
			$stmt->bind_param('iis', $user_id, $user_id, $category);
		else if ( $type == 'active' )
			// $stmt->bind_param('iiis', $user_id, $user_id, $user_id, $category);
			$stmt->bind_param('iisis', $user_id, $user_id, $category, $user_id, $category);
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
		$stmt = $db_connection->prepare( 'SELECT A.userID, A.First_Name, A.Last_Name, A.Username, A.Role, B.Name 
										  FROM OFFICERS A
 										  LEFT JOIN SHIFTS B ON B.Id = A.Shift_id ' );
		$stmt->execute();
		$stmt->bind_result( $userID, $First_Name, $Last_Name, $Username, $Role, $Shift_name);
		while($stmt->fetch())
		{
			$tmp = ["id" => $userID,
					"firstName" => $First_Name, "lastName" => $Last_Name,
					"username" => $Username, "role" => $Role, 'shift_name' => $Shift_name];
			array_push($officers, $tmp);
		}
		$stmt->close();
		$db_connection->close();
		return $officers;
	}

	function getShifts(){
		global $db_connection;
		$shifts = [];
		$stmt = $db_connection->prepare( 'SELECT Id, Name, From_time, To_time, Status FROM SHIFTS' );
		$stmt->execute();
		$stmt->bind_result($Id, $Name, $From_time, $To_time, $Status);
		while($stmt->fetch())
		{
			$tmp = ["id" => $Id,
					"sName" => $Name, 
					"fTime" => $From_time,
					"tTime" => $To_time,
					"sStatus" => $Status];
			array_push($shifts, $tmp);
		}
		$stmt->close();
		$db_connection->close();
		return $shifts;
	}


 	function getTimeout(){
 		global $db_connection;
 		$minutes = 0;
 		$stmt = $db_connection->prepare( 'SELECT Session_Timeout FROM SETTINGS' );
		$stmt->execute();
		$stmt->bind_result($min);
		while($stmt->fetch())
		{
			$minutes = $min;
		}
		$stmt->close();
		$db_connection->close();
	    return $minutes;

 	}


 	function getLatLong(){
 		global $db_connection;
 		$lat_long = [];
 		$stmt = $db_connection->prepare( 'SELECT Latitude, Longitude FROM SETTINGS' );
		$stmt->execute();
		$stmt->bind_result($lat, $lon);

		while($stmt->fetch())
		{
			$lat_long = [
						"lat" => $lat,
						"lon" => $lon
						];
		}
		$stmt->close();
		$db_connection->close();
	    return $lat_long;

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
	function addWatchOrder($desc, $address, $lat, $long, $addDate, $expDate, $startDate, $startTime, $expTime, $zone, $businessName,                                 $ownerName, $woRequester, $phone, $woInstruction, $eName, $eAddress, $ePhone, $createdby) {
		global $db_connection;
		$result = ['Added' => false];
		$sql = "INSERT INTO WATCH_ORDERS (`Desc`,`Address`,`Lat`,`Lng`,`AddDate`,`ExpDate`,`StartDate`, `StartTime`, `ExpTime`,`Zone`,`BusinessName`,`OwnerName`,`WORequester`,`Phone`,`WOInstruction`,`EName`,`EAddress`,`EPhone`,`CreatedBy`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
		$stmt = $db_connection->prepare($sql);
		if (!$stmt->bind_param('ssddsssssssssssssss', $desc, $address, $lat, $long, $addDate, $expDate, $startDate, $startTime, $expTime, $zone, $businessName, $ownerName, $woRequester, $phone, $woInstruction, $eName, $eAddress, $ePhone, $createdby))
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
		$sql = "SELECT `Id`,`Desc`,`Address`,`Lat`,`Lng`,`AddDate`,`ExpDate`,`StartDate`, `StartTime`, `ExpTime`,`Zone`,`BusinessName`,`OwnerName`,`WORequester`,`Phone`,`WOInstruction`,`EName`,`EAddress`,`EPhone`,`CreatedBy` FROM WATCH_ORDERS";
		$stmt = $db_connection->prepare($sql);
		$stmt->execute();
		$stmt->bind_result($Id, $Desc, $Address, $Lat, $Lng, $AddDate, $ExpDate, $StartDate, $StartTime, $ExpTime, $Zone, $BusinessName, $OwnerName, $WORequester, $Phone, $WOInstruction, $EName, $EAddress, $EPhone, $CreatedBy);
		while($stmt->fetch()){
			$tmp = ["Id" => $Id,
			"Desc" => $Desc,
			"Address" => $Address,
			"Lat" => $Lat,
			"Lng" => $Lng,
			"AddDate" => $AddDate,
			"ExpDate" => $ExpDate,
			"StartDate" => $StartDate,
			"StartTime" => $StartTime,
			"ExpTime" => $ExpTime, 
			"Zone" => $Zone,
			"BusinessName" => $BusinessName, 
			"OwnerName" => $OwnerName, 
			"WORequester" => $WORequester, 
			"Phone" => $Phone, 
			"WOInstruction" => $WOInstruction, 
			"EName" => $EName, 
			"EAddress" => $EAddress,
			"EPhone" => $EPhone,
			"CreatedBy" => $CreatedBy];
			array_push($orders, $tmp);
		}
		$stmt->close();
		$db_connection->close();
		return $orders;
	}
	function getWatchOrdersForUser($user_id) {
		global $db_connection;
		$orders = [];

		$sql =" SELECT a.Id
				     , a.Desc
				     , a.Address
				     , a.Lat
				     , a.Lng
				     , a.AddDate
				     , a.ExpDate
				     , a.StartDate
				     , a.StartTime
				     , a.ExpTime
				     , a.Zone
				     , a.BusinessName
				     , a.OwnerName
				     , a.WORequester
				     , a.Phone
				     , a.WOInstruction
				     , a.EName
					 , a.EAddress
				     , a.EPhone
				     , a.CreatedBy
				     , b.is_selected
				  FROM WATCH_ORDERS a
				  left join watch_orders_tracking b on a.Id = b.watch_orders_id and b.officers_userid = ?";

		$stmt = $db_connection->prepare($sql);
	    $stmt->bind_param('s', $user_id);
		$stmt->execute();
		$stmt->bind_result($Id, $Desc, $Address, $Lat, $Lng, $AddDate, $ExpDate, $StartDate, $StartTime, $ExpTime, $Zone, $BusinessName, $OwnerName, $WORequester, $Phone, $WOInstruction, $EName, $EAddress, $EPhone, $CreatedBy, $is_selected);
		while($stmt->fetch()){
			$tmp = ["Id" => $Id,
			"Desc" => $Desc,
			"Address" => $Address,
			"Lat" => $Lat,
			"Lng" => $Lng,
			"AddDate" => $AddDate,
			"ExpDate" => $ExpDate,
			"StartDate" => $StartDate,
			"StartTime" => $StartTime,
			"ExpTime" => $ExpTime, 
			"Zone" => $Zone,
			"BusinessName" => $BusinessName, 
			"OwnerName" => $OwnerName, 
			"WORequester" => $WORequester, 
			"Phone" => $Phone, 
			"WOInstruction" => $WOInstruction, 
			"EName" => $EName, 
			"EAddress" => $EAddress,
			"EPhone" => $EPhone,
			"CreatedBy" => $CreatedBy,
			"is_selected" => $is_selected];

			array_push($orders, $tmp);
		}
		$stmt->close();
		$db_connection->close();
		return $orders;
	}
	function editWatchOrder($id, $desc, $address, $lat, $lng, $expDate, $startDate, $startTime, $expTime, $zone, $businessName,                                 $ownerName, $woRequester, $phone, $woInstruction, $eName, $eAddress, $ePhone) {
		global $db_connection;
		$result = ["Updated" => false];
		$table = "WATCH_ORDERS";
		$sql = "UPDATE $table SET `Desc`=?, `Address`=?, `Lat`=?, `Lng`=?, `ExpDate`=?, `StartDate`=?, `StartTime`=?, `ExpTime`=?,`Zone`=?,`BusinessName`=?,`OwnerName`=?,`WORequester`=?,`Phone`=?,`WOInstruction`=?,`EName`=?,`EAddress`=?,`EPhone`=? WHERE `Id`=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('sssssssssssssssssd', $desc, $address, $lat, $lng, $expDate, $startDate, $startTime, $expTime, $zone, $businessName, $ownerName, $woRequester, $phone, $woInstruction, $eName, $eAddress, $ePhone, $id) )
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
	function editWatchOrderTracking($wo_id, $user_id, $is_selected) {
		global $db_connection;
		$result = ["Updated" => false];
		$table = "watch_orders_tracking";
		$sql = "UPDATE $table SET `is_selected`=? WHERE `watch_orders_id` = ? and `officers_userid`=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('ddd', $is_selected, $wo_id, $user_id))
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
	function addUser($first_name, $last_name, $username, $password, $role, $shift) {
		global $db_connection;
		global $crypter;
		$hash_password = $crypter->hash($password);

		$result = ['Added' => false,'Username' => $username, 'Password' => $hash_password];
		$sql = "INSERT INTO OFFICERS (First_Name, Last_Name, Username, Password, Role, Shift_id) VALUES (?,?,?,?,?,?)";
		$stmt = $db_connection->prepare($sql);
		if (!$stmt->bind_param('ssssss', $first_name, $last_name, $username, $hash_password, $role, $shift))
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		if (!$stmt->execute())
			return $result;

    	$result['Added'] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	function editUser($id, $first_name, $last_name, $username, $role, $shift) {
		global $db_connection;
		$result = ["Updated" => false];
		$table = "OFFICERS";
		$sql = "UPDATE $table SET First_Name=?, Last_Name=?, Username=?, Role=?, Shift_id = ?
		        WHERE userID=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('sssssd', $first_name, $last_name, $username, $role, $shift, $id) )
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



	 //ADD NEW SHIFT TO DATABASE
	function addShift($shift_name, $from_time, $to_time, $status) {
		global $db_connection;

		$result = ['Added' => false,'Name' => $shift_name, 'From_time' => $from_time, 'To_time' => $to_time, 'Status' => $status];
		$sql = "INSERT INTO SHIFTS (Name, From_time, To_time, Status) VALUES (?,?,?,?)";
		$stmt = $db_connection->prepare($sql);
		if (!$stmt->bind_param('ssss', $shift_name, $from_time, $to_time, $status))
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		if (!$stmt->execute())
			return $result;

    	$result['Added'] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}



	function editShift($id, $shift_name, $from_time, $to_time, $status) {
		global $db_connection;
		$result = ["Updated" => false];
		$table = "SHIFTS";
		$sql = "UPDATE $table SET Name=?, From_time=?, To_time=?, Status=?
		        WHERE Id=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('ssssd', $shift_name, $from_time, $to_time, $status, $id) )
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

	function removeShift($id) {
		global $db_connection;
		$result = ["Removed" => false];
		$table = "SHIFTS";
		$sql = "DELETE FROM $table
		        WHERE Id=?";
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
					//"catid" => $cat_id,
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
		// $statusArray = $this->GetStatusArray();

        global $db_connection;
        $logs = [];
     //    $sql = "call ViewDocuments()";
     //    $stmt = $db_connection->prepare($sql);
     //    $stmt->execute();
     //    $stmt->bind_result($First_Name, $Last_Name, $Document_Name, $DOC,
					// 		$Uploaded, $Started, $Completed, $Duration, $Status);
     //        	        file_put_contents('/Users/darilyspereira/Desktop/test.txt', $First_Name, FILE_APPEND);

     //        while($stmt->fetch()){

     //                $tmp = [
					// 	"Full_Name" => $First_Name.' '.$Last_Name,
					// 	"Document_Name" => $Document_Name,
					// 	"DOC" => $DOC,
					// 	"Uploaded" => $Uploaded,
					// 	"Started" => $Started,
					// 	"Completed" => $Completed,
					// 	"Duration" => $Duration < 0 ? '0.00 Sec' : $Duration,
					// 	"Status" => $Status
					// ];
     //                array_push($logs, $tmp);
     //        }
     //        $stmt->close();



        $rs = $db_connection->query( "CALL ViewDocuments()");
		while($row = $rs->fetch_object())
		{

              $tmp = [
						"Full_Name" => $row->firstname.' '.$row->lastname,
						"Document_Name" => $row->docuname,
						"DOC" => $row->logdoc,
						"Uploaded" => $row->uploaddt,
						"Started" => $row->startdt,
						"Completed" => $row->enddt,
						"Duration" => $row->duration < 0 ? '0.00 Sec' : $row->duration,
						"Status" => $row->statusdesc
					];
                    array_push($logs, $tmp);

		}


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

		//Get all category id and name. Add blanks temporarly for the shifts
		$sql = "SELECT category_ID, Category_Name FROM CATEGORIES";
		$stmt = $db_connection->prepare($sql);
		$stmt->execute();
		$stmt->bind_result($id, $name);
		while($stmt->fetch()) {
			array_push($categories, ["id" => $id, "name" => $name, "shifts" => ""]);
		}

		//append the shifts for each category
		$arrayCount = count($categories);
		for ($i=0; $i <$arrayCount  ; $i++) {
			$category_shift = []; 

			//get category id
			$cat_id = $categories[$i]['id'];

			//execute new query
			$sql2 = 'SELECT distinct s.Name FROM CATEGORY_SHIFT_ACCESS csa LEFT JOIN SHIFTS s ON csa.Shift_Id = s.Id WHERE csa.Category_ID = ?';
			$stmt2 = $db_connection->prepare($sql2);
			$stmt2->bind_param('i', $cat_id);
			$stmt2->execute();
			$stmt2->bind_result($shifts);

			while($stmt2->fetch()) {
				array_push($category_shift, ["shift" => $shifts]);
			}

			$category_shift = array_reverse($category_shift);
			// convert the array of shifts to a string separated by coma
			$shift_string = "";
			for($j = 0 ; $j< count($category_shift) ; $j++){
				if ($j == (count($category_shift) -1)){
					$shift_string = $category_shift[$j]['shift'] . $shift_string;
				}
				else {
					$shift_string = ", ". $category_shift[$j]['shift'] . $shift_string;
				}
			}

		// append the string of shifts to the category array	
			$categories[$i]['shifts'] = $shift_string;
		}

		$stmt->close();
		$db_connection->close();
		return $categories;
	}



   //************GET AUTHORIZED CATEGORIES*************************************
	function getAuthorizedCategories($id){
		global $db_connection;
		$authCategories = [];
		//Get shift id for the logged in officer
		$sql = "SELECT Shift_id FROM OFFICERS WHERE userid = ?";
		$stmt = $db_connection->prepare($sql);
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$stmt->bind_result($sId);
		$shift_id = array();
		while($stmt->fetch()) {
			$shift_id[] = $sId;
		}
		$shiftId = $shift_id[0];

		//if officer can see ALL shifts then get all categories
		if ($shiftId == 1){
			$sql2 = "SELECT Category_ID, Category_Name FROM CATEGORIES";
			$stmt2 = $db_connection->prepare($sql2);
			$stmt2->execute();
			$stmt2->bind_result($cat_id, $name);
			while($stmt2->fetch()) {
				array_push($authCategories, ["id" => $cat_id, "name" => $name]);
			}
		}
		//else, get only the authorized categories for that officer
		else{
			$sql3 = "SELECT Category_ID, Category_Name 
					FROM categories c 
					WHERE c.Category_ID in (
											SELECT csa.Category_ID 
											from category_shift_access csa, officers o 
											where (csa.Shift_Id = o.Shift_id and o.UserID = ?)
											UNION 
                        					SELECT csa1.Category_ID from category_shift_access csa1 where csa1.Shift_Id = 1) ";
			$stmt3 = $db_connection->prepare($sql3);
			$stmt3->bind_param('i', $id);
			$stmt3->execute();
			$stmt3->bind_result($cat_id, $name);
			while($stmt3->fetch()) {
				array_push($authCategories, ["id" => $cat_id, "name" => $name]);
			}
		}

		$stmt->close();
		$db_connection->close();
		return $authCategories;

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
				  AND d.Manual_Archived = false
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
	function addCategory($name, $shift) {
		global $db_connection;
		$result = ['Added' => false,'name' => $name];
		
		//***********INSERT NEW CATEGORY INTO CATEGORIES TABLE*****************
		$sql = "INSERT INTO CATEGORIES (Category_Name) VALUES (?)";
		$stmt = $db_connection->prepare($sql);
		if (!$stmt->bind_param('s', $name)){
			echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (!$stmt->execute()){
			return $result;
		}


		//***********GET ID OF NEW CATEGORY CREATED*****************
		$sql2 = "SELECT Category_ID FROM CATEGORIES WHERE Category_Name = (?)";
		$stmt2 = $db_connection->prepare($sql2);
		if (!$stmt2->bind_param('s', $name)){
			echo "Binding parameters failed: (" . $stmt2->errno . ") " . $stmt2->error;
		}
		if (!$stmt2->execute()){
			return $result;
		}
		$cat_id = array();
		$stmt2->bind_result($id);
		while ( $stmt2->fetch()) {
			$cat_id[] = $id;
		}
		$cId = $cat_id[0];


		// //***********INSERT NEW CATEGORY AND SHIFT(S) INTO CATEGORY_SHIFT_ACCESS TABLE**************
		for ($i = 0 ; $i < count($shift); $i++){
		$sql3 = "INSERT INTO CATEGORY_SHIFT_ACCESS(Category_ID, Shift_Id) VALUES (?, ?)";
		$stmt3 = $db_connection->prepare($sql3);
			if (!$stmt3->bind_param('ii', $cId, $shift[$i])){
				echo "Binding parameters failed: (" . $stmt3->errno . ") " . $stmt3->error;
			}
			if (!$stmt3->execute()){
				return $result;
			}
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


		$sql2 = "DELETE FROM CATEGORY_SHIFT_ACCESS WHERE Category_ID = ?";
		$stmt2 = $db_connection->prepare($sql2);
		if( !$stmt2->bind_param('i', $cat_id) )
			return $result;

		if (!$stmt2->execute())
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
			
			//remove entries from documents table
			$query = 'DELETE FROM Documents WHERE Document_ID = ?';
			$stmt = $db_connection->prepare($query);

			if( !$stmt->bind_param('i', $document_id) )
				return $result;
			if (!$stmt->execute())
				return $result;

			//remove entries from user_doc_status
			$query2 = 'DELETE FROM user_doc_status WHERE DocumentId = ?';
			$stmt2 = $db_connection->prepare($query2);

			if( !$stmt2->bind_param('i', $document_id) )
				return $result;
			if (!$stmt2->execute())
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
	function updateCategory($cat_id, $cat_name, $shift) {
		global $db_connection;
		$result = ["Updated" => false];

		//****************UPDATE CATEGORIES TABLE*****************
		$sql = "UPDATE CATEGORIES SET Category_Name=? WHERE category_ID=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('sd', $cat_name, $cat_id)){
			return $result;
		}
		if (!$stmt->execute()){
			return $result;
		}

		//************REMOVE ALL ROWS FROM CATEGORY_SHIFT_ACCESS TABLES**********
		$sql2 = "DELETE FROM CATEGORY_SHIFT_ACCESS WHERE Category_ID = ?";
		$stmt2 = $db_connection->prepare($sql2);
		if( !$stmt2->bind_param('i', $cat_id) )
			return $result;

		if (!$stmt2->execute())
			return $result;


		//************INSERT NEW SELECTIONS INTO CATEGORY_SHIFT_ACCESS TABLES**********
		for ($i = 0 ; $i < count($shift); $i++){
		$sql3 = "INSERT INTO CATEGORY_SHIFT_ACCESS(Category_ID, Shift_Id) VALUES (?, ?)";
		$stmt3 = $db_connection->prepare($sql3);
			if (!$stmt3->bind_param('ii', $cat_id, $shift[$i])){
				echo "Binding parameters failed: (" . $stmt3->errno . ") " . $stmt3->error;
			}
			if (!$stmt3->execute()){
				return $result;
			}
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


	//UPDATE SESSION TIMEOUT IN DATABASE
	function updateTimeout($time) {
		global $db_connection;
		$result = ["Updated" => false];
		$sql = "UPDATE SETTINGS SET Session_Timeout=?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('s', $time)) return $result;
		if (!$stmt->execute()) return $result;
		$result["Updated"] = true;
		$stmt->close();
		$db_connection->close();
		return $result;
	}

	//UPDATE APP DEFAULT MAP LATITUDE AND LONGITUDE
	function updateLatLong($lat, $lon) {
		global $db_connection;
		$result = ["Updated" => false];
		$sql = "UPDATE settings SET Latitude= ?,Longitude = ?";
		$stmt = $db_connection->prepare($sql);
		if( !$stmt->bind_param('dd', $lat, $lon)) return $result;
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
		
		//update DOCUMENTS table
		$sql = "Update DOCUMENTS set Document_Name=?,Category_ID=?,Pinned=? where document_ID =?";
		$rs = $db_connection->prepare($sql);
		if(!$rs->bind_param('siii',$name,$categories,$pinned,$id))
				return "Bind paramenter error";

		if(!$rs->execute())
			return "Execute Error";
		$rs->close();

		//update user_doc_staus if row exist
		$sql2 = "Update USER_DOC_STATUS set CategoryId=? where DocumentId =?";
		$rs2 = $db_connection->prepare($sql2);
		if(!$rs2->bind_param('ii',$categories,$id))
				return "Bind paramenter error";

		if(!$rs2->execute())
			return "Execute Error";
		$rs2->close();

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
			$sql = "INSERT INTO USER_DOC_STATUS (StartDateTime, EndDateTime, DocumentId,OfficerId,StatusId, CategoryId, IsArchived)
						   values(now(),now(),?,?,?,?,0) ";
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
