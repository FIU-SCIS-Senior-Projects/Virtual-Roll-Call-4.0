<?php
	class JCryptProvider
	{
	    protected $encrypted = '';

		//Must be updated to match production environment
		function __construct()
		{
			global $db_connection;
			$un = 'root';
			$pw = 'VirtualRollCall';
			$dbName = 'VIRTUAL_ROLL_CALL';
			$address = 'localhost';
			$db_connection = new mysqli($address, $un, $pw, $dbName);

			if ($db_connection->connect_errno > 0) {
				die('Unable to connect to database[' . $db_connection->connect_error . ']');
			}
		}

		function getOfficers()
		{
			global $db_connection;
			$officers = [];
			$query = 'SELECT userID, Password FROM OFFICERS';
			$stmt = $db_connection->prepare($query);
			$stmt->execute();
			$stmt->bind_result( $userID, $Password );
			while($stmt->fetch())
			{
				$hashed_password = $this->providerHash($Password);
				$tmp = ["id" => $userID, "password" => $hashed_password];
				array_push($officers, $tmp);
			}
			$stmt->close();
			return $officers;
		}

		
		function HashPasswords()
		{
			global $db_connection;
			$result = ['rows_affected' => 0];
			$counter = 0;

			$officers = $this->getOfficers();

			foreach( $officers as $officer)
			{
				$query = "UPDATE OFFICERS SET Password = ? WHERE userID = ? ";
				$stmt = $db_connection->prepare($query);

				if( !$stmt->bind_param('si', $officer['password'], $officer['id']) )
					return $result;
				if(!$stmt->execute()) return $result;
				else $counter++;
				$stmt->close();
			}

			$result['rows_affected'] = $counter;
			return $result;
		}

	     /*
		 * Create a random string
		 * @author	XEWeb <>
		 * @param $length the length of the string to create
		 * @return $str the string
		 */
		function randomSalt($length = 16) 
		{
			$str = "";
			$characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
			$max = count($characters) - 1;
			for ($i = 0; $i < $length; $i++) 
			{
				$rand = mt_rand(0, $max);
				$str .= $characters[$rand];
			}
			return $str;
		}

		function cryptString( $password )
		{
			return $this->secure_hash( crypt( $password , $this->randomSalt() ) );
		}

		function providerHash( $password )
		{
	    	$options = [ 'cost' => 10 ];
	        return password_hash($password, PASSWORD_BCRYPT, $options);
		}

		function close()
		{
			global $db_connection;
			$db_connection->close();
		}
	}

	$provider = new JCryptProvider();
	$result = $provider->HashPasswords();
	$provider->close();

	die(json_encode($result));