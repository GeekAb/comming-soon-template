<?php
	
	if(!$_POST)
		header( 'Location: index.html' ) ;

	define("MYSQL_HOST","localhost");
    define("MYSQL_USERNAME","*****"); //Your DB Username
    define("MYSQL_PASSWD","******");  //Your DB Password
    define("MYSQL_DB","*******");     //Your Database

    //Table structe I have used.
    /*
    CREATE TABLE IF NOT EXISTS `email_ids` (
    `email` varchar(100) NOT NULL,
    `ip` varchar(20) NOT NULL,
    `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ENGINE=InnoDB DEFAULT CHARSET=latin1;
    */
    $email = $_POST['email'];


    if($email==''){
    	echo json_encode(array('status'=>500,'message'=>'Sorry! But E-Mail can not be blank.'));
    	exit;
    }

    //Connecting to Database
    $processorObj = new Processor();
    $linkId = $processorObj->connect();

    if($linkId){
    	$email = $processorObj->escapeMimic($email);

    	/*Check if email Id already exist*/
    	$queryString = "SELECT 1 FROM email_ids WHERE email='$email'";

    	$processorObj->query( $queryString );

    	if(!$processorObj->singlerecord(MYSQLI_ASSOC))
    	{
    		$queryString = "INSERT INTO email_ids(`email`,`ip`) VALUES('$email','".$_SERVER['REMOTE_ADDR']."')";
    		$processorObj->query( $queryString );

    		echo json_encode(array('status'=>200,'message'=>'Yea! We got your Id. You will get a mail once we launch. Thanks'));
    	}
    	else
    	{
    		echo json_encode(array('status'=>409,'message'=>'Hey! We already have your Id.'));
    	}
    }
    else 
    	echo json_encode(array('status'=>500,'message'=>'Sorry! There is some issue.Please try again.'));

	class Processor
	{ 
		var $link_ID  = false;         
		var $query_ID = 0;          
		var $currRecord   = array();
		var $row;                    
		var $loginerror = "";

		var $errno    = 0;           
		var $error    = "";
		var $done 	  = 1;
 
		//-------------------------------------------
		//    Connects to the database
		//-------------------------------------------
		function connect()
		{
			if(!$this->link_ID)
				$this->link_ID = mysqli_connect('p:localhost',MYSQL_USERNAME,MYSQL_PASSWD,MYSQL_DB);

			// Check connection
			if(mysqli_connect_errno($this->link_ID))
			{
				return false;
			}
			return true;
		} // end function connect
 
		//-------------------------------------------
		//    Queries the database
		//-------------------------------------------
		function query( $queryString )
		{
			$this->connect();

			$this->query_ID = mysqli_query($this->link_ID, $queryString);
			$this->row = 0;
			$this->errno = mysqli_errno($this->link_ID);
			$this->error = mysqli_error($this->link_ID	);
			if( !$this->query_ID )
				return false;
			return $this->query_ID;
		} // end function query

		//-------------------------------------------
		//    Insert record into database
		//-------------------------------------------
		function insert( $queryString )
		{
			$this->query_ID = mysqli_query($this->link_ID, $queryString);
			$this->row = 0;
			$this->errno = mysqli_errno($this->link_ID);
			$this->error = mysqli_error($this->link_ID	);
			if( !$this->query_ID )
				return false;
			return $this->lastId();
		} // end function insert
 
		//-------------------------------------------
		//    Retrieves a single record
		//-------------------------------------------
		function singlerecord($fetch_type)
		{
			$this->currRecord = $this->query_ID->fetch_array($fetch_type);
			$stat = is_array( $this->currRecord );
			return $stat;
		} // end function singlerecord
 
		//-------------------------------------------
		//    Returns Escaped string
		//-------------------------------------------
		function escapeMimic($data)
		{
			if(!$this->link_ID) $this->connect();

			if(is_array($data))
				return array_map(__METHOD__, $data);

			if(!empty($data) && is_string($data)) {
				return $this->link_ID->real_escape_string($data);
			}
		}
}

?>