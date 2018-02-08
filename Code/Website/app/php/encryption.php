<?php
	class JCrypter
	{
	    public function hash( $password ) 
	    {
	    	$options = [ 'cost' => 10 ];
	        return password_hash($password, PASSWORD_BCRYPT, $options);
	    }

	    public function secure_hash( $password )
	    {
	    	return sha1( $password );
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

		function verify( $password, $hash )
		{
			return password_verify( $password, $hash) ? 1 : 0;
		}

		public function cryptString( $password )
		{
			$salt = $this->randomSalt();
			return $this->secure_hash( crypt( $password , $salt ) );
		}

		public function rehash( $hashed_password, $options )
		{
			if ( passowrd_needs_rehash( $hashed_password, PASSWORD_DEFAULT, $options))
			{
				// the password needs to be rehashed as it was not generated with
				// the current default algorithm or not created with the cost
				// parameter 12
				$hash = password_hash($password, PASSWORD_DEFAULT, $options);

				//Save in DB
			}
		}
	}