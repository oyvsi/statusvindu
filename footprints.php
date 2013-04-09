<?php
   require_once 'config.php';


function fetchAmount($query,$type) {
	if($dummydata != TRUE) {
		print("TRUE");
		$result = mssql_query($query);
		$amount = mssql_fetch_assoc($result);
		return array($type => $amount['computed']);
	}
	else {
		print("FALSE");
		return array($type => 12);
	}
}

if(isset($_GET['type'])) {
	if($_GET['type'] == 'Closed') {
			$query = "select COUNT(mrID) FROM dbo.MASTER2 WHERE DATEDIFF(day, mrSUBMITDATE, GETDATE()) = 0 AND mrSTATUS = 'Closed'";
			echo json_encode(fetchAmount($query, 'Closed'), JSON_NUMERIC_CHECK);
	} 
	elseif ($_GET['type'] == 'Recieved') {
		$query = "select COUNT(mrID) FROM dbo.MASTER2 WHERE DATEDIFF(day, mrSUBMITDATE, GETDATE()) = 0";
		echo json_encode(fetchAmount($query, 'Recieved'), JSON_NUMERIC_CHECK);
	}
}
